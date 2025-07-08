<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $perPage = 10;

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        try {
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

            User::create($validated + ['createdDateTime' => now()]);

            return redirect()->route('dashboard')->with('success', 'User added successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $user->delete();

        return redirect()->route('dashboard')->with('success', 'User deleted successfully!');
    }

    public function index(Request $request)
    {
        $dateInput = $request->input('date');
        $date = $dateInput && strtotime($dateInput) ? Carbon::parse($dateInput) : null;
    
        [$start, $end] = $this->resolveDateRange($request);
    
        // Apply filters
        $query = User::query();
    
        if ($date) {
            $query->whereDate('createdDateTime', $date);
        }
    
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail1', 'like', "%{$search}%")
                  ->orWhere('mail2', 'like', "%{$search}%");
            });
        }
    
        // Include login count within date range
        $query->withCount(['signIns as login_count' => function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        }]);
    
        $users = $query->paginate(10)->withQueryString();
    
        // Calculate total logged in and not logged in counts
        $loggedInCount = User::whereHas('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        })->count();
    
        $notLoggedInCount = User::whereDoesntHave('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        })->count();
    
        return view('dashboard', compact('users', 'date', 'loggedInCount', 'notLoggedInCount'));
    }
    
    

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // Default to 'this_month' if no range is provided
        $range = $request->input('range', 'this_month');
    
        // Default to 'SCM' if no system is provided
        $system = $request->input('system', 'SCM');
    
        // Resolve date range
        [$start, $end] = $this->resolveDateRange($range);
    
        // Build sign-ins query
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
    
    public function loggedInUsers(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $search = $request->input('search');
    
        $query = User::whereHas('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        });
    
        // Apply search if provided
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
    

    public function notLoggedInUsers(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $search = $request->input('search');
    
        $query = User::whereDoesntHave('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        });
    
        // Apply search if provided
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
