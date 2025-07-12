<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginFilterService;
use App\Services\DashboardStatsService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SigninLog;

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

        $start = null;
        $end = null;
        if ($range = $request->input('range')) {
            switch ($range) {
                case 'this_month':
                    $start = now()->startOfMonth();
                    $end = now()->endOfDay();
                    break;
                case 'last_month':
                    $start = now()->subMonth()->startOfMonth();
                    $end = now()->subMonth()->endOfMonth();
                    break;
                case 'last_3_months':
                    $start = now()->subMonths(3)->startOfMonth();
                    $end = now()->endOfDay();
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

        $selectedSystem = $request->input('system', 'Odoo'); // Default to 'Odoo'
        if ($selectedSystem) {
            $query->whereHas('signIns', function ($query) use ($selectedSystem) {
                $query->where('system', $selectedSystem); // Filter on 'system' column
            });
        }

        Log::debug('User query: ' . $query->toSql());
        Log::debug('Bindings: ', $query->getBindings());

        $users = $query->withCount('signIns')->paginate(10);

        if ($users->isEmpty()) {
            Log::debug('No users found. Start: ' . ($start ?? 'null') . ', End: ' . ($end ?? 'null') . ', System: ' . $selectedSystem);
        }

        $systems = SigninLog::select('system')
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('date_utc', [$start, $end]);
            })
            ->distinct()
            ->pluck('system')
            ->toArray();
        Log::debug('Available systems: ', $systems);

        $stats = $this->statsService->generateStats($filters);

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