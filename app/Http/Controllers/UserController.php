<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Controller responsible for managing user records, 
 * login activity tracking, and associated reporting.
 */
class UserController extends Controller
{
    /**
     * Number of records to display per page for pagination.
     *
     * @var int
     */
    protected $perPage = 10;

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in the database after validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming form data
            $validated = $request->validate([
                'id' => 'required|string|unique:users,id',
                'userPrincipalName' => 'required|unique:users,userPrincipalName',
                'displayName' => 'nullable|string',
                'surname1' => 'nullable|string',
                'surname2' => 'nullable|string',
                'mail1' => 'nullable|email',
                'mail2' => 'nullable|email',
                'givenName1' => 'nullable|string',
                'givenName2' => 'nullable|string',
                'userType' => 'nullable|string',
                'jobTitle' => 'nullable|string',
                'department' => 'nullable|string',
                'accountEnabled' => 'boolean',
            ]);

            // Persist new user record
            User::create($validated + ['createdDateTime' => now()]);

            return redirect()->route('dashboard')->with('success', 'User added successfully!');
        } catch (ValidationException $e) {
            // Return validation errors to the form
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            // Catch and return any unexpected errors
            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove a user by ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $user->delete();

        return redirect()->route('dashboard')->with('success', 'User deleted successfully!');
    }

    /**
     * Display a paginated list of users, with optional date and search filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $dateInput = $request->input('date');
        $date = $dateInput && strtotime($dateInput) ? Carbon::parse($dateInput) : null;

        [$start, $end] = $this->resolveDateRange($request);

        $query = User::query();

        // Filter by created date if provided
        if ($date) {
            $query->whereDate('createdDateTime', $date);
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail1', 'like', "%{$search}%")
                  ->orWhere('mail2', 'like', "%{$search}%");
            });
        }

        // Count sign-ins within the date range
        $query->withCount(['signIns as login_count' => function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        }]);

        $users = $query->paginate(10)->withQueryString();

        // Calculate logged in / not logged in stats for dashboard cards
        $loggedInCount = User::whereHas('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        })->count();

        $notLoggedInCount = User::whereDoesntHave('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        })->count();

        return view('dashboard', compact('users', 'date', 'loggedInCount', 'notLoggedInCount'));
    }

    /**
     * Display details and sign-in activity for a specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $range = $request->input('range', 'this_month');
        $system = $request->input('system', 'SCM');

        [$start, $end] = $this->resolveDateRange($range);

        $signInsQuery = $user->signIns()
            ->whereBetween('date_utc', [$start, $end]);

        if ($system) {
            $signInsQuery->where('system', $system);
        }

        $signIns = $signInsQuery->orderBy('date_utc', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('user-details', compact('user', 'signIns', 'start', 'end', 'system', 'range'));
    }

    /**
     * Display a list of users who have logged in within a date range, with optional search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function loggedInUsers(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $search = $request->input('search');

        $query = User::whereHas('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail1', 'like', "%{$search}%")
                  ->orWhere('mail2', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount(['signIns as login_count' => function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        }])->paginate(10)->withQueryString();

        return view('users.logged-in', [
            'users' => $users,
            'range' => $request->input('range', 'this_month'),
            'search' => $search
        ]);
    }

    /**
     * Display a list of users who have not logged in within a date range, with optional search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function notLoggedInUsers(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $search = $request->input('search');

        $query = User::whereDoesntHave('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail1', 'like', "%{$search}%")
                  ->orWhere('mail2', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.not-logged-in', [
            'users' => $users,
            'range' => $request->input('range', 'this_month'),
            'search' => $search
        ]);
    }

    /**
     * Resolve the date range for reports and filters based on request parameters.
     *
     * @param  \Illuminate\Http\Request|string|null  $input
     * @return array
     */
    protected function resolveDateRange($input = null)
    {
        if ($input instanceof Request) {
            if ($input->has('range')) {
                return $this->resolveDateRange($input->input('range'));
            } elseif ($input->has('date') && strtotime($input->input('date'))) {
                $date = Carbon::parse($input->input('date'));
                return [$date->startOfDay(), $date->endOfDay()];
            } else {
                return [now()->startOfMonth(), now()->endOfDay()];
            }
        }

        // Handle string input for range
        switch ($input) {
            case 'last_month':
                $start = now()->subMonthNoOverflow()->startOfMonth();
                $end = now()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'last_3_months':
                $start = now()->subMonthsNoOverflow(3)->startOfMonth();
                $end = now()->endOfDay();
                break;
            default:
                $start = now()->startOfMonth();
                $end = now()->endOfDay();
        }

        return [$start, $end];
    }
}
