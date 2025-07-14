<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SigninLog;
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
        $system = $request->input('system', 'Odoo');
        $search = $request->input('search');
    
        if ($request->ajax()) {
            DB::enableQueryLog();
            Log::info('AJAX Request', [
                'range' => $rangeInput,
                'system' => $system,
                'search' => $search,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString()
            ]);
    
            $query = User::query();
            if ($request->filled('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'like', "%{$search}%")
                        ->orWhere('userPrincipalName', 'like', "%{$search}%")
                        ->orWhere('mail1', 'like', "%{$search}%")
                        ->orWhere('mail2', 'like', "%{$search}%");
                });
            }
    
            if ($system) {
                $query->whereHas('signIns', function ($q) use ($start, $end, $system) {
                    $q->whereBetween('date_utc', [$start, $end])
                        ->where(DB::raw('LOWER(system)'), strtolower($system));
                });
            } else {
                $query->whereHas('signIns', function ($q) use ($start, $end) {
                    $q->whereBetween('date_utc', [$start, $end]);
                });
            }
    
            $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system) {
                $q->whereBetween('date_utc', [$start, $end]);
                if ($system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system));
                }
            }])
            ->with(['signIns' => function ($q) use ($start, $end, $system) {
                $q->whereBetween('date_utc', [$start, $end]);
                if ($system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system));
                }
                $q->latest('date_utc');
            }]);
    
            $users = $query->paginate($this->perPage)->withQueryString();
            $totalDays = $start->diffInDays($end) + 1;
    
            $logins = SigninLog::whereBetween('date_utc', [$start, $end])
                ->when($system, function ($query) use ($system) {
                    return $query->where(DB::raw('LOWER(system)'), strtolower($system));
                })
                ->get();
            $loggedInEmails = $logins->pluck('username')->filter()->unique()->map(fn($email) => strtolower(trim($email)));
            $totalUsers = User::count();
            $loggedInCount = $loggedInEmails->isEmpty() ? 0 : User::whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();
            $notLoggedInCount = $totalUsers - $loggedInCount;
    
            // Get all distinct systems, sorted alphabetically
            $systems = SigninLog::select('system')->distinct()->pluck('system')->sort()->values()->toArray();
            Log::info('Systems for Dropdown', ['systems' => $systems]);
    
            Log::debug('Executed Queries', DB::getQueryLog());
            Log::info('AJAX Response Data', [
                'totalUsers' => $totalUsers,
                'loggedInCount' => $loggedInCount,
                'notLoggedInCount' => $notLoggedInCount,
                'userCount' => $users->count(),
                'sampleUsers' => $users->take(5)->toArray()
            ]);
            DB::disableQueryLog();
    
            return response()->json([
                'totalUsers' => $totalUsers,
                'loggedInCount' => $loggedInCount,
                'notLoggedInCount' => $notLoggedInCount,
                'users' => $users->items(),
                'totalDays' => $totalDays,
                'pagination' => (string) $users->links()
            ]);
        }
    
        $query = User::query();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                    ->orWhere('userPrincipalName', 'like', "%{$search}%")
                    ->orWhere('mail1', 'like', "%{$search}%")
                    ->orWhere('mail2', 'like', "%{$search}%");
            });
        }
    
        if ($system) {
            $query->whereHas('signIns', function ($q) use ($start, $end, $system) {
                $q->whereBetween('date_utc', [$start, $end])
                    ->where(DB::raw('LOWER(system)'), strtolower($system));
            });
        } else {
            $query->whereHas('signIns', function ($q) use ($start, $end) {
                $q->whereBetween('date_utc', [$start, $end]);
            });
        }
    
        $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system) {
            $q->whereBetween('date_utc', [$start, $end]);
            if ($system) {
                $q->where(DB::raw('LOWER(system)'), strtolower($system));
            }
        }])
        ->with(['signIns' => function ($q) use ($start, $end, $system) {
            $q->whereBetween('date_utc', [$start, $end]);
            if ($system) {
                $q->where(DB::raw('LOWER(system)'), strtolower($system));
            }
            $q->latest('date_utc');
        }]);
    
        $users = $query->paginate($this->perPage)->withQueryString();
        $logins = SigninLog::whereBetween('date_utc', [$start, $end])
            ->when($system, function ($query) use ($system) {
                return $query->where(DB::raw('LOWER(system)'), strtolower($system));
            })
            ->get();
        $loggedInEmails = $logins->pluck('username')->filter()->unique()->map(fn($email) => strtolower(trim($email)));
        $totalUsers = User::count();
        $loggedInCount = $loggedInEmails->isEmpty() ? 0 : User::whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();
        $notLoggedInCount = $totalUsers - $loggedInCount;
    
        // Get all distinct systems, sorted alphabetically
        $systems = SigninLog::select('system')->distinct()->pluck('system')->sort()->values()->toArray();
        Log::info('Systems for Dropdown (Non-AJAX)', ['systems' => $systems]);
    
        $rangeLabel = $this->getRangeLabel($rangeInput);
        $systemInput = $system ?: 'All Systems';
    
        return view('dashboard', compact('users', 'loggedInCount', 'notLoggedInCount', 'rangeInput', 'system', 'totalUsers', 'rangeLabel', 'systemInput', 'systems'));
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $range = $request->input('range', 'this_month');
        $system = $request->input('system', 'Windows Sign In'); // Default to a valid application

        [$start, $end] = $this->resolveDateRange($range);

        $signIns = $user->signIns()
            ->whereBetween('date_utc', [$start, $end])
            ->orderBy('date_utc', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        // Get recent applications used by this user
        $recentApplications = $user->signIns()
            ->whereBetween('date_utc', [$start, $end])
            ->select('application', 'system', DB::raw('COUNT(*) as usage_count'), DB::raw('MAX(date_utc) as last_used'))
            ->whereNotNull('application')
            ->groupBy('application', 'system')
            ->orderBy('last_used', 'desc')
            ->limit(10)
            ->get();

        return view('users.show', compact('user', 'signIns', 'start', 'end', 'system', 'range', 'recentApplications'));
    }

    public function loggedInUsers(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $search = $request->input('search');
        $range = $request->input('range', 'this_month');
        $system = $request->input('system', 'Windows Sign In'); // Default to a valid application

        $query = User::whereHas('signIns', function ($q) use ($start, $end, $system) {
            $q->whereBetween('date_utc', [$start, $end]);
            if ($system) {
                $q->where(DB::raw('LOWER(system)'), strtolower($system));
            }
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                    ->orWhere('userPrincipalName', 'like', "%{$search}%")
                    ->orWhere('mail1', 'like', "%{$search}%")
                    ->orWhere('mail2', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount(['signIns as login_count' => function ($q) use ($start, $end, $system) {
            $q->whereBetween('date_utc', [$start, $end]);
            if ($system) {
                $q->where(DB::raw('LOWER(application)'), strtolower($system)); // Use 'application'
            }
        }])->paginate($this->perPage)->withQueryString();

        return view('users.logged-in', compact('users', 'range', 'search', 'system'));
    }

    public function notLoggedInUsers(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $search = $request->input('search');
        $range = $request->input('range', 'this_month');
        $system = $request->input('system', 'Windows Sign In'); // Default to a valid application

        $query = User::whereDoesntHave('signIns', function ($q) use ($start, $end, $system) {
            $q->whereBetween('date_utc', [$start, $end]);
            if ($system) {
                $q->where(DB::raw('LOWER(system)'), strtolower($system));
            }
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                    ->orWhere('userPrincipalName', 'like', "%{$search}%")
                    ->orWhere('mail1', 'like', "%{$search}%")
                    ->orWhere('mail2', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($this->perPage)->withQueryString();

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

        switch ($input) {
            case 'last_month':
                $start = now()->subMonthNoOverflow()->startOfMonth();
                $end = now()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'last_3_months':
                $start = now()->subMonthsNoOverflow(3)->startOfMonth();
                $end = now()->endOfDay();
                break;
            default: // this_month or custom
                $start = now()->startOfMonth();
                $end = now()->endOfDay();
        }

        Log::info('Resolved Date Range', ['start' => $start, 'end' => $end]);

        return [$start, $end];
    }

    private function getRangeLabel($range)
    {
        return match ($range) {
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last_3_months' => 'Last 3 Months',
            'custom' => 'Custom Range',
            default => 'This Month',
        };
    }
}