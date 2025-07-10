<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InteractiveSignIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


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
        [$start, $end] = $this->resolveDateRange($request);
        $rangeInput = $request->input('range', 'this_month');

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                    ->orWhere('userPrincipalName', 'like', "%{$search}%")
                    ->orWhere('mail1', 'like', "%{$search}%")
                    ->orWhere('mail2', 'like', "%{$search}%");
            });
        }

        $query->withCount(['interactiveSignIns as login_count' => function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
        }]);

        $query->with(['interactiveSignIns' => function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end])->latest('date_utc');
        }]);

        $users = $query->paginate(10)->withQueryString();

        // ✅ Updated: Count logged-in users using username/email instead of user_id
        $loggedInEmails = InteractiveSignIn::whereBetween('date_utc', [$start, $end])
            ->pluck('username')
            ->filter()
            ->unique()
            ->map(fn($email) => strtolower(trim($email)));

        $loggedInCount = User::whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();
        $notLoggedInCount = User::whereNotIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();

        return view('dashboard', compact('users', 'loggedInCount', 'notLoggedInCount', 'rangeInput'));
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $range = $request->input('range', 'this_month');
        $system = $request->input('system' , 'SCM');

        [$start, $end] = $this->resolveDateRange($range);

        $signInsQuery = $user->interactiveSignIns()
            ->whereBetween('date_utc', [$start, $end]);

           

        $signIns = $signInsQuery->orderBy('date_utc', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('user-details', compact('user', 'signIns', 'start', 'end', 'system', 'range'));
    }

    public function loggedInUsers(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $search = $request->input('search');
        $range = $request->input('range', 'this_month');
        $system = $request->input('system');
    
        $query = User::whereHas('interactiveSignIns', function ($q) use ($start, $end, $system) {
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
    
        $users = $query->withCount(['interactiveSignIns as login_count' => function ($q) use ($start, $end, $system) {
            $q->whereBetween('date_utc', [$start, $end]);
    
        }])->paginate(10)->withQueryString();
    
        return view('users.logged-in', compact('users', 'range', 'search', 'system'));
    }
    

    public function notLoggedInUsers(Request $request)
{
    [$start, $end] = $this->resolveDateRange($request);
    $search = $request->input('search');
    $range = $request->input('range', 'this_month');
    $system = $request->input('system');

    $query = User::whereDoesntHave('interactiveSignIns', function ($q) use ($start, $end, $system) {
        $q->whereBetween('date_utc', [$start, $end]);

        // if ($system) {
        //     $q->where('resource_display_name', $system);
        // }
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

    return view('users.not-logged-in', compact('users', 'range', 'search', 'system'));
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

        // switch ($input) {
        //     case 'last_month':
        //         $start = now()->subMonthNoOverflow()->startOfMonth();
        //         $end = now()->subMonthNoOverflow()->endOfMonth();
        //         break;
        //     case 'last_3_months':
        //         $start = now()->subMonthsNoOverflow(3)->startOfMonth();
        //         $end = now()->endOfDay();
        //         break;
        //     default:
        //         $start = now()->startOfMonth();
        //         $end = now()->endOfDay();
        // }


        switch ($input) {
            case 'last_month':
                $start = Carbon::create(2025, 7, 1)->startOfDay();
                $end   = Carbon::create(2025, 7, 1)->endOfDay();
                break;
            case 'last_3_months':
                $start = Carbon::create(2025, 7, 1)->startOfDay();
                $end   = Carbon::create(2025, 7, 2)->endOfDay();
                break;
            default:
                $start = Carbon::create(2025, 7, 1)->startOfDay();
                $end   = Carbon::create(2025, 7, 2)->endOfDay();
        }
        
        Log::info('Resolved Date Range', ['start' => $start, 'end' => $end]);


        return [$start, $end];
    }
}
