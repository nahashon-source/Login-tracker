<?php

namespace App\Services;

use App\Models\SigninLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\LoginFilterService;

class DashboardStatsService
{
    protected $filterService;

    public function __construct(LoginFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function generateStats(array $filters)
    {
        ['startDate' => $startDate, 'endDate' => $endDate, 'system' => $system, 'search' => $search] = $filters;
        $totalDays = (int) ($startDate->diffInDays($endDate) + 1);

        // 🔹 Users with filters - show all users who have used the system, with date-filtered login counts
        $usersQuery = User::query()
            ->select('id', 'displayName', 'userPrincipalName')
            ->withCount(['signIns as sign_ins_count' => function ($q) use ($filters) {
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
            }]);

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%$search%")
                  ->orWhere('userPrincipalName', 'like', "%$search%");
            });
        }

        $users = $usersQuery->orderBy('displayName')->paginate(17);

        foreach ($users as $user) {
            $uniqueDays = $user->signIns->pluck('date_utc')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->count();

            $user->missedDays = max(0, $totalDays - $uniqueDays);
            $user->lastLoginDate = optional($user->signIns->first())->date_utc;
        }

        // 🔹 Logged in / not logged in count
        $loggedInQuery = User::query();
        
        $loggedInCount = $loggedInQuery->whereHas('signIns', function ($q) use ($filters) {
            // Apply date filters
            if (!empty($filters['startDate'])) {
                $q->where('date_utc', '>=', $filters['startDate']);
            }
            if (!empty($filters['endDate'])) {
                $q->where('date_utc', '<=', $filters['endDate']);
            }
            // Apply system filter to signin logs
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
        })->count();

        // Total users should always be the total count in the database
        $totalUsers = User::count();
        
        // For "not logged in" calculation, get users who have used the system but didn't login in the date range
        if ($system) {
            // Get users who have ever used this system
            $mappedSystems = collect(config('systemmap'))
                ->filter(function ($mappedSystem) use ($system) {
                    return $mappedSystem === $system;
                })
                ->keys()
                ->toArray();
            if (!empty($mappedSystems)) {
                $usersInSystem = User::whereHas('signIns', function ($q) use ($mappedSystems) {
                    $q->where(function ($query) use ($mappedSystems) {
                        foreach ($mappedSystems as $mappedSystem) {
                            $query->orWhere('application', 'LIKE', "%$mappedSystem%");
                        }
                    });
                })->count();
                $notLoggedInCount = max(0, $usersInSystem - $loggedInCount);
            } else {
                $notLoggedInCount = $totalUsers - $loggedInCount;
            }
        } else {
            // If no system filter, use all users
            $notLoggedInCount = $totalUsers - $loggedInCount;
        }

        // 🔹 All sign-ins for listing
        $signIns = DB::table('signin_logs')
            ->join('users', 'signin_logs.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'signin_logs.date_utc',
                'signin_logs.application',
                'users.displayName',
                'users.userPrincipalName as email'
            )
            ->whereBetween('signin_logs.date_utc', [$startDate, $endDate])
            ->when($system, fn($q) => $q->where('signin_logs.application', $system))
            ->orderByDesc('signin_logs.date_utc')
            ->get();

        // 🔹 Total and last login
        $loginQuery = $this->filterService->applyDateAndSystemFilters(SigninLog::query(), $filters);

        $totalLogins = $loginQuery->count();
        $lastLogin   = $loginQuery->max('date_utc');

        return compact(
            'users', 'loggedInCount', 'notLoggedInCount', 'totalUsers',
            'signIns', 'totalLogins', 'lastLogin', 'totalDays'
        );
    }
}
