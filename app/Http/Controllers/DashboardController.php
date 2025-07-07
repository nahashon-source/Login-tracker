<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InteractiveSignIn;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : null;
        
        $users = User::withCount(['signIns' => function ($query) use ($date) {
            $query->whereBetween('date_utc', [
                Carbon::today()->subDays(30)->startOfDay(),
                Carbon::today()->endOfDay()
            ]);
            if ($date) {
                $query->whereDate('date_utc', $date);
            }
        }])->with(['signIns' => function ($query) use ($date) {
            $query->whereBetween('date_utc', [
                Carbon::today()->subDays(30)->startOfDay(),
                Carbon::today()->endOfDay()
            ]);
            if ($date) {
                $query->whereDate('date_utc', $date);
            }
        }])->paginate(10);

        return view('dashboard', compact('users', 'date'));
    }
}