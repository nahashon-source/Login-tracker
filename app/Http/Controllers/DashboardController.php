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
        $date = null;

        if ($dateInput && strtotime($dateInput)) {
            $date = Carbon::parse($dateInput);
        }

        $users = User::withCount(['signIns as sign_ins_count' => function ($query) use ($date) {
                $query->where('date_utc', '>=', Carbon::today()->subDays(30));

                if ($date) {
                    $query->whereDate('date_utc', $date);
                }
            }])
            ->with(['signIns' => function ($query) use ($date) {
                $query->where('date_utc', '>=', Carbon::today()->subDays(30))
                      ->orderBy('date_utc', 'desc')
                      ->limit(5);

                if ($date) {
                    $query->whereDate('date_utc', $date);
                }
            }])
            ->orderBy('displayName')
            ->paginate(10);

        return view('dashboard', compact('users', 'date'));
    }
}
