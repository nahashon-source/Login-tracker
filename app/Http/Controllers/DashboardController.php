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
use Illuminate\Support\Facades\Config;

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
            $query->where(function ($q) use ($searchTerm) {
                $q->where('displayName', 'like', "%{$searchTerm}%")
                  ->orWhere('userPrincipalName', 'like', "%{$searchTerm}%");
            });
        }
        
        // Filter by system - show users who have used the selected system
        $selectedSystem = $request->input('system', 'SCM');
        if ($selectedSystem) {
            // Get all mapped system names from config
            $mappedSystems = collect(config('systemmap'))
                ->filter(function ($system) use ($selectedSystem) {
                    return $system === $selectedSystem;
                })
                ->keys()
                ->toArray();
            
            if (!empty($mappedSystems)) {
                $query->whereHas('signIns', function ($query) use ($mappedSystems) {
                    $query->where(function ($q) use ($mappedSystems) {
                        foreach ($mappedSystems as $mappedSystem) {
                            $q->orWhere('application', 'LIKE', "%$mappedSystem%");
                        }
                    });
                });
            }
        } else {
            // Only filter by activity when no system is selected
            // This ensures we show all users with recent activity when no system filter is applied
            $query->whereHas('signIns');
        }


        Log::debug('User query: ' . $query->toSql());
        Log::debug('Bindings: ', $query->getBindings());

        $users = $query->withCount(['signIns as sign_ins_count' => function ($q) use ($filters) {
                           // Apply date filters to count only logins in the selected period
                           if (!empty($filters['startDate'])) {
                               $q->where('date_utc', '>=', $filters['startDate']);
                           }
                           if (!empty($filters['endDate'])) {
                               $q->where('date_utc', '<=', $filters['endDate']);
                           }
                           // Apply system filter
                           if (!empty($filters['system'])) {
                               $mappedSystems = collect(config('systemmap'))
                                   ->filter(function ($system) use ($filters) {
                                       return $system === $filters['system'];
                                   })
                                   ->keys()
                                   ->toArray();
                               if (!empty($mappedSystems)) {
                                   $q->where(function ($query) use ($mappedSystems) {
                                       foreach ($mappedSystems as $mappedSystem) {
                                           $query->orWhere('application', 'LIKE', "%$mappedSystem%");
                                       }
                                   });
                               }
                           }
                       }])
                       ->with(['signIns' => function ($q) use ($filters) {
                           // Show recent logins filtered by date and system
                           if (!empty($filters['startDate'])) {
                               $q->where('date_utc', '>=', $filters['startDate']);
                           }
                           if (!empty($filters['endDate'])) {
                               $q->where('date_utc', '<=', $filters['endDate']);
                           }
                           if (!empty($filters['system'])) {
                               $mappedSystems = collect(config('systemmap'))
                                   ->filter(function ($system) use ($filters) {
                                       return $system === $filters['system'];
                                   })
                                   ->keys()
                                   ->toArray();
                               if (!empty($mappedSystems)) {
                                   $q->where(function ($query) use ($mappedSystems) {
                                       foreach ($mappedSystems as $mappedSystem) {
                                           $query->orWhere('application', 'LIKE', "%$mappedSystem%");
                                       }
                                   });
                               }
                           }
                           $q->orderByDesc('date_utc')->limit(5);
                       }])
                       ->paginate(17)
                       ->appends($request->query());

        if ($users->isEmpty()) {
            Log::debug('No users found. Start: ' . ($start ?? 'null') . ', End: ' . ($end ?? 'null') . ', System: ' . $selectedSystem);
        }

        $systems = $this->filterService->getSystemList();
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
                'pagination'         => $users->withQueryString()->links()->toHtml(),
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
            'selectedRange'      => $filters['range'],
            'selectedSystem'     => $selectedSystem,
        ]);
    }

    public function showDashboard()
    {
        return view('dashboard');
    }
}