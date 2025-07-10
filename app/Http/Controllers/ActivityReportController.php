<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InteractiveSignIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller responsible for generating user activity reports
 * over a specified time period.
 */
class ActivityReportController extends Controller
{
    /**
     * Display a report of user activity (login count, first login, last login)
     * for the selected date range, defaulting to the past 30 days.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function userActivityReport(Request $request)
    {
        // Determine start date: if a date is provided, use its start of day; otherwise, 30 days ago
        $startDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->subDays(30);

        // Determine end date: if a date is provided, use its end of day; otherwise, now
        $endDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->endOfDay()
            : now();

        // Query for user activity within the date range
        $activity = User::select([
                'users.id',
                'users.displayName',
                'users.userPrincipalName',

                // Aggregate data: total logins, first and last login timestamps
                DB::raw('COUNT(interactive_sign_ins.user_id) AS login_count'),
                DB::raw('MIN(interactive_sign_ins.date_utc) AS first_login'),
                DB::raw('MAX(interactive_sign_ins.date_utc) AS last_login')
            ])
            // Left join to include users even if they have no sign-ins
            ->leftJoin('interactive_sign_ins', function ($join) use ($startDate, $endDate) {
                $join->on('interactive_sign_ins.user_id', '=', 'users.id')
                     ->whereBetween('interactive_sign_ins.date_utc', [$startDate, $endDate]);
            })
            // Group by required user fields for aggregation
            ->groupBy('users.id', 'users.displayName', 'users.userPrincipalName')
            // Order by most active users first
            ->orderByDesc('login_count')
            // Paginate the result set (20 per page)
            ->paginate(20);

        // Return the activity report view with the collected data
        return view('activity_report.index', compact('activity', 'startDate', 'endDate'));
    }
}
