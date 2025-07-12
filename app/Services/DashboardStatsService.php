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

        // 🔹 Users with filters
        $usersQuery = User::query()
            ->select('id', 'displayName', 'userPrincipalName')
            ->withCount(['signIns as sign_ins_count' => function ($q) use ($filters) {
                $this->filterService->applyDateAndSystemFilters($q, $filters);
            }])
            ->with(['signIns' => function ($q) use ($filters) {
                $this->filterService->applyDateAndSystemFilters($q, $filters)
                     ->orderByDesc('date_utc')
                     ->limit(5);
            }]);

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%$search%")
                  ->orWhere('userPrincipalName', 'like', "%$search%");
            });
        }

        $users = $usersQuery->orderBy('displayName')->paginate(10);

        foreach ($users as $user) {
            $uniqueDays = $user->signIns->pluck('date_utc')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->count();

            $user->missedDays = max(0, $totalDays - $uniqueDays);
            $user->lastLoginDate = optional($user->signIns->first())->date_utc;
        }

        // 🔹 Logged in / not logged in count
        $loggedInCount = User::whereHas('signIns', function ($q) use ($filters) {
            $this->filterService->applyDateAndSystemFilters($q, $filters);
        })->count();

        $totalUsers = User::count();
        $notLoggedInCount = $totalUsers - $loggedInCount;

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
