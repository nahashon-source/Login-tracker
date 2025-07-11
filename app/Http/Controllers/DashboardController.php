<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Inputs
        $rangeInput  = $request->input('range');
        $systemInput = $request->input('system');
        $search      = $request->input('search');

        // Date Range Determination
        switch ($rangeInput) {
            case 'this_month':
                $startDate  = Carbon::now()->startOfMonth();
                $endDate    = Carbon::now()->endOfMonth();
                $rangeLabel = 'This Month';
                break;
            case 'last_month':
                $startDate  = Carbon::now()->subMonth()->startOfMonth();
                $endDate    = Carbon::now()->subMonth()->endOfMonth();
                $rangeLabel = 'Last Month';
                break;
            case 'last_3_months':
                $startDate  = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate    = Carbon::now()->endOfMonth();
                $rangeLabel = 'Last 3 Months';
                break;
            default:
                $startDate  = Carbon::now()->subDays(30);
                $endDate    = Carbon::now();
                $rangeLabel = 'Last 30 Days';
        }

        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Users with Sign-in Counts
        $users = User::select('users.id', 'users.displayName', 'users.userPrincipalName') // Explicitly select columns
            ->withCount([
                'signIns as sign_ins_count' => function ($query) use ($startDate, $endDate, $systemInput) {
                    $query->whereBetween('date_utc', [$startDate, $endDate]);
                    if ($systemInput) {
                        $query->where('application', $systemInput);
                    }
                }
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'like', "%{$search}%")
                      ->orWhere('userPrincipalName', 'like', "%{$search}%");
                });
            })
            ->with(['signIns' => function ($query) use ($startDate, $endDate, $systemInput) {
                $query->whereBetween('date_utc', [$startDate, $endDate])
                      ->orderBy('date_utc', 'desc')
                      ->limit(5);
                if ($systemInput) {
                    $query->where('application', $systemInput);
                }
            }])
            ->orderBy('displayName')
            ->paginate(10);

        // Logged-in User Count
        $loggedInCount = User::whereHas('signIns', function ($query) use ($startDate, $endDate, $systemInput) {
                $query->whereBetween('date_utc', [$startDate, $endDate]);
                if ($systemInput) {
                    $query->where('application', $systemInput);
                }
            })->count();

        $notLoggedInCount = User::count() - $loggedInCount;

        // Recent Sign-Ins list
        $signInsQuery = DB::table('signin_logs')
            ->join('users', 'signin_logs.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'signin_logs.date_utc',
                'signin_logs.application',
                'users.displayName',
                'users.userPrincipalName as email'
            )
            ->whereBetween('signin_logs.date_utc', [$startDate, $endDate]);

        if ($systemInput) {
            $signInsQuery->where('signin_logs.application', $systemInput);
        }

        $signIns = $signInsQuery
            ->orderBy('signin_logs.date_utc', 'desc')
            ->get();

        // Systems list
        $systems = [
            'D365 LIVE', 'FIT ERP', 'FIT EXPRESS', 'FIT EXPRESS UAT',
            'FIT ERP UAT', 'ODOO', 'OPS', 'OPS UAT'
        ];

        // Debug: Uncomment to inspect $users data
        // dd($users);

        return view('dashboard', compact(
            'users',
            'rangeInput',
            'rangeLabel',
            'loggedInCount',
            'notLoggedInCount',
            'totalDays',
            'signIns',
            'systemInput',
            'systems'
        ));
    }

    public function showDashboard()
    {
        return view('dashboard');
    }
}