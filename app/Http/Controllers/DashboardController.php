<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginFilterService;
use App\Services\DashboardStatsService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SigninLog;
use App\Models\System;

class DashboardController extends Controller
{
    protected $filterService;
    protected $statsService;

    public function __construct(LoginFilterService $filterService, DashboardStatsService $statsService)
    {
        $this->filterService = $filterService;
        $this->statsService = $statsService;
    }

    public function index(Request $request)
    {
$filters = $this->filterService->parseFilters($request);
        
        $query = User::query();

        if ($searchTerm = $request->input('search')) {
            $query->where('displayName', 'like', "%{$searchTerm}%")
                  ->orWhere('userPrincipalName', 'like', "%{$searchTerm}%");
        }
        
        // Filter by selected system
        $selectedSystem = $request->input('system');
        if ($selectedSystem) {
            $query->whereHas('systems', function ($query) use ($selectedSystem) {
                $query->where('name', $selectedSystem);
            });
        }

        $start = null;
        $end = null;
        if ($range = $request->input('range')) {
            switch ($range) {
                case 'this_month':
                    $start = Carbon::create(2025, 7, 1, 0, 0, 0);
                    $end = Carbon::create(2025, 7, 31, 23, 59, 59);
                    break;
                case 'last_month':
                    $start = Carbon::create(2025, 6, 1, 0, 0, 0);
                    $end = Carbon::create(2025, 6, 30, 23, 59, 59);
                    break;
                case 'last_3_months':
                    $start = Carbon::create(2025, 5, 1, 0, 0, 0);
                    $end = Carbon::create(2025, 7, 31, 23, 59, 59);
                    break;
                case 'custom':
                    $startDate = $request->input('start_date');
                    $endDate = $request->input('end_date');
                    if ($startDate && $endDate) {
                        $start = Carbon::parse($startDate)->startOfDay();
                        $end = Carbon::parse($endDate)->endOfDay();
                    }
                    break;
            }
            if ($start && $end) {
                $query->whereHas('signIns', function ($query) use ($start, $end) {
                    $query->whereBetween('date_utc', [$start, $end]);
                });
            }
        }


        Log::debug('User query: ' . $query->toSql());
        Log::debug('Bindings: ', $query->getBindings());

        $users = $query->withCount('signIns')->paginate(10);

        if ($users->isEmpty()) {
            Log::debug('No users found. Start: ' . ($start ?? 'null') . ', End: ' . ($end ?? 'null') . ', System: ' . $selectedSystem);
        }

        $systems = System::orderBy('name')->pluck('name')->toArray();
        Log::debug('Available systems: ', $systems);

        $stats = $this->statsService->generateStats($filters);

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'users'              => $users->items(),
                'totalUsers'         => $stats['totalUsers'],
                'loggedInCount'      => $stats['loggedInCount'],
                'notLoggedInCount'   => $stats['notLoggedInCount'],
                'totalLogins'        => $stats['totalLogins'],
                'lastLogin'          => $stats['lastLogin'],
                'signIns'            => $stats['signIns'],
                'totalDays'          => $stats['totalDays'],
                'rangeInput'         => $filters['range'],
                'rangeLabel'         => $filters['rangeLabel'],
                'systemInput'        => $selectedSystem,
                'systems'            => $systems,
                'pagination'         => $users->withQueryString()->links()->render(),
            ]);
        }

        return view('dashboard', [
            'users'              => $users,
            'totalUsers'         => $stats['totalUsers'],
            'loggedInCount'      => $stats['loggedInCount'],
            'notLoggedInCount'   => $stats['notLoggedInCount'],
            'totalLogins'        => $stats['totalLogins'],
            'lastLogin'          => $stats['lastLogin'],
            'signIns'            => $stats['signIns'],
            'totalDays'          => $stats['totalDays'],
            'rangeInput'         => $filters['range'],
            'rangeLabel'         => $filters['rangeLabel'],
            'systemInput'        => $selectedSystem,
            'systems'            => $systems,
        ]);
    }

    public function showDashboard()
    {
        return view('dashboard');
    }
}