<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateInput = $request->input('date');
        $rangeInput = $request->input('range');
        $systemInput = $request->input('system');
        $date = null;

        // Determine start date and range label
        if ($dateInput && strtotime($dateInput)) {
            $date = Carbon::parse($dateInput);
            $rangeLabel = $date->format('Y-m-d');
            $startDate = $date->copy()->startOfDay();
        } else {
            $startDate = Carbon::today()->subDays(30);
            $rangeLabel = 'Last 30 Days';
            if ($rangeInput) {
                switch ($rangeInput) {
                    case 'this_month':
                        $startDate = Carbon::now()->startOfMonth();
                        $rangeLabel = 'This Month';
                        break;
                    case 'last_month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $rangeLabel = 'Last Month';
                        break;
                    case 'last_3_months':
                        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                        $rangeLabel = 'Last 3 Months';
                        break;
                }
            }
        }

        // Fetch users with sign-in counts filtered by date and optional system
        $users = User::withCount([
            'signIns as sign_ins_count' => function ($query) use ($startDate, $date, $systemInput) {
                $query->where('date_utc', '>=', $startDate);
                if ($date) {
                    $query->whereDate('date_utc', $date);
                }
                if ($systemInput) {
                    $query->where('system', $systemInput);
                }
            }
        ])
        ->with([
            'signIns' => function ($query) use ($startDate, $date, $systemInput) {
                $query->where('date_utc', '>=', $startDate)
                      ->orderBy('date_utc', 'desc')
                      ->limit(5);
                if ($date) {
                    $query->whereDate('date_utc', $date);
                }
                if ($systemInput) {
                    $query->where('system', $systemInput);  // ✅ Corrected column here
                }
            }
        ])
        ->orderBy('displayName')
        ->paginate(10);

        // Counts for dashboard cards
        $loggedInCount = User::whereHas('signIns', function ($query) use ($startDate, $date, $systemInput) {
            $query->where('date_utc', '>=', $startDate);
            if ($date) {
                $query->whereDate('date_utc', $date);
            }
            if ($systemInput) {
                $query->where('system', $systemInput);  // ✅ Corrected column here too
            }
        })->count();

        $notLoggedInCount = User::count() - $loggedInCount;

        // Return view
        return view('dashboard', compact(
            'users',
            'date',
            'rangeInput',
            'systemInput',
            'rangeLabel',
            'loggedInCount',
            'notLoggedInCount'
        ));
    }
}
