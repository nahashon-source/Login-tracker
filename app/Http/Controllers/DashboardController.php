<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateInput   = $request->input('date');
        $rangeInput  = $request->input('range');
        $search      = $request->input('search');
        $date        = null;

        if ($dateInput && strtotime($dateInput)) {
            $date       = Carbon::parse($dateInput);
            $rangeLabel = $date->format('Y-m-d');
            $startDate  = $date->copy()->startOfDay();
        } else {
            $startDate  = Carbon::today()->subDays(30);
            $rangeLabel = 'Last 30 Days';

            if ($rangeInput) {
                switch ($rangeInput) {
                    case 'this_month':
                        $startDate  = Carbon::now()->startOfMonth();
                        $rangeLabel = 'This Month';
                        break;
                    case 'last_month':
                        $startDate  = Carbon::now()->subMonth()->startOfMonth();
                        $rangeLabel = 'Last Month';
                        break;
                    case 'last_3_months':
                        $startDate  = Carbon::now()->subMonths(3)->startOfMonth();
                        $rangeLabel = 'Last 3 Months';
                        break;
                }
            }
        }

        $totalDays = $date ? 1 : Carbon::today()->diffInDays($startDate) + 1;

        // Eager load users with sign-in count
        $users = User::withCount([
            'signIns as sign_ins_count' => function ($query) use ($startDate, $date) {
                $query->where('date_utc', '>=', $startDate);
                if ($date) {
                    $query->whereDate('date_utc', $date);
                }
            }
        ])
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail1', 'like', "%{$search}%");
            });
        })
        ->with([
            'signIns' => function ($query) use ($startDate, $date) {
                $query->where('date_utc', '>=', $startDate)
                      ->orderBy('date_utc', 'desc')
                      ->limit(5);
                if ($date) {
                    $query->whereDate('date_utc', $date);
                }
            }
        ])
        ->orderBy('displayName')
        ->paginate(10);

        // Logged in users count
        $loggedInCount = User::whereHas('signIns', function ($query) use ($startDate, $date) {
            $query->where('date_utc', '>=', $startDate);
            if ($date) {
                $query->whereDate('date_utc', $date);
            }
        })->count();

        $notLoggedInCount = User::count() - $loggedInCount;

        // Recent Sign-Ins list (adjusted: no interactive_sign_ins.id column used)
        $signIns = DB::table('interactive_sign_ins')
        ->join('users', 'interactive_sign_ins.user_id', '=', 'users.id')
        ->select(
            'interactive_sign_ins.user_id',
            'interactive_sign_ins.date_utc',
            'interactive_sign_ins.application',
            'users.displayName',
            'users.userPrincipalName'
        )
        ->where('interactive_sign_ins.date_utc', '>=', $startDate)
        ->orderBy('interactive_sign_ins.date_utc', 'desc')
        ->get();
    

        return view('dashboard', compact(
            'users',
            'date',
            'rangeInput',
            'rangeLabel',
            'loggedInCount',
            'notLoggedInCount',
            'totalDays',
            'signIns'
        ));
    }

    public function showDashboard()
    {
        return view('dashboard');
    }
}
