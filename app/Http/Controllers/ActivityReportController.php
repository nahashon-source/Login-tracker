<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InteractiveSignIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityReportController extends Controller
{
    public function userActivityReport(Request $request)
    {
        $startDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->subDays(30);

        $endDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->endOfDay()
            : now();

            $activity = User::select([
                'users.id',
                'users.displayName',
                'users.userPrincipalName',
                DB::raw('COUNT(interactive_sign_ins.id) AS login_count'),
                DB::raw('MIN(interactive_sign_ins.date_utc) AS first_login'),
                DB::raw('MAX(interactive_sign_ins.date_utc) AS last_login')
            ])
            ->leftJoin('interactive_sign_ins', function ($join) use ($startDate, $endDate) {
                $join->on('interactive_sign_ins.user_id', '=', 'users.id')
                     ->whereBetween('interactive_sign_ins.date_utc', [$startDate, $endDate]);
            })
            ->groupBy('users.id', 'users.displayName', 'users.userPrincipalName')
            ->orderByDesc('login_count')
            ->paginate(20); 
            

        return view('activity_report.index', compact('activity', 'startDate', 'endDate'));
    }
}
