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
            $query->where(function ($q) use ($searchTerm) {
                $q->where('displayName', 'like', "%{$searchTerm}%")
                  ->orWhere('userPrincipalName', 'like', "%{$searchTerm}%");
            });
        }
        
        // Filter by system - show users who are assigned to the selected system
        $selectedSystem = $request->input('system');
        if ($selectedSystem) {
            $query->whereHas('systems', function ($query) use ($selectedSystem) {
                $query->where('name', $selectedSystem);
            });
        } else {
            // Only filter by activity when no system is selected
            // This ensures we show all users with recent activity when no system filter is applied
            $query->whereHas('signIns');
        }


        Log::debug('User query: ' . $query->toSql());
        Log::debug('Bindings: ', $query->getBindings());

        $users = $query->withCount('signIns')
                       ->with(['signIns' => function($q) {
                           $q->orderBy('date_utc', 'desc')->limit(1);
                       }])
                       ->paginate(10)
                       ->appends($request->query());

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