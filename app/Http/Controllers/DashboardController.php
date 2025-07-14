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
        
        // Filter by system - show all users who have ever used the selected system
        $selectedSystem = $request->input('system');
        if ($selectedSystem) {
            $mappedSystem = collect(config('systemmap'))
                ->filter(function ($system) use ($selectedSystem) {
                    return $system === $selectedSystem;
                })
                ->keys()->first();
            if ($mappedSystem) {
                $query->whereHas('signIns', function ($query) use ($mappedSystem) {
                    $query->where('application', 'LIKE', "%$mappedSystem%");
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