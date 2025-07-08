<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Controller responsible for handling the dashboard overview,
 * summarizing user activity within the past 30 days or on a specific date.
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard with users and their recent sign-in activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Retrieve the date input from the request (if any)
        $dateInput = $request->input('date');
        $date = null;

        // Validate and parse the date input if it's a valid date string
        if ($dateInput && strtotime($dateInput)) {
            $date = Carbon::parse($dateInput);
        }

        // Query users along with:
        // - Count of sign-ins within the past 30 days (or on the given date)
        // - Their latest 5 sign-ins within the same period
        $users = User::withCount([
                'signIns as sign_ins_count' => function ($query) use ($date) {
                    // Always restrict to sign-ins within the past 30 days
                    $query->where('date_utc', '>=', Carbon::today()->subDays(30));

                    // If a specific date is provided, further filter by that date
                    if ($date) {
                        $query->whereDate('date_utc', $date);
                    }
                }
            ])
            ->with([
                'signIns' => function ($query) use ($date) {
                    // Fetch sign-ins within the past 30 days
                    $query->where('date_utc', '>=', Carbon::today()->subDays(30))
                          ->orderBy('date_utc', 'desc')   // Most recent sign-ins first
                          ->limit(5);                     // Limit to 5 sign-ins per user

                    // If a specific date is provided, further narrow down to that date
                    if ($date) {
                        $query->whereDate('date_utc', $date);
                    }
                }
            ])
            // Order users alphabetically by display name
            ->orderBy('displayName')
            // Paginate results — 10 users per page
            ->paginate(10);

        // Return the dashboard view with the retrieved users and date filter
        return view('dashboard', compact('users', 'date'));
    }
}
