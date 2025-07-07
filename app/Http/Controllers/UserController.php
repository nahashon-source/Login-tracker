<?php 


namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|string|unique:users,id',
                'userPrincipalName' => 'required|unique:users,userPrincipalName',
                'displayName' => 'nullable|string',
                'surname1' => 'nullable|string',
                'surname2' => 'nullable|string',
                'mail1' => 'nullable|email',
                'mail2' => 'nullable|email',
                'givenName1' => 'nullable|string',
                'givenName2' => 'nullable|string',
                'userType' => 'nullable|string',
                'jobTitle' => 'nullable|string',
                'department' => 'nullable|string',
                'accountEnabled' => 'boolean',
            ]);

            User::create($validated + ['createdDateTime' => now()]);

            return redirect()->route('dashboard')->with('success', 'User added successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $user->delete();

        return redirect()->route('dashboard')->with('success', 'User deleted successfully!');
    }


    public function index(Request $request)
    {
        $dateInput = $request->input('date');
        $date = $dateInput && strtotime($dateInput) ? Carbon::parse($dateInput) : null;
    
        $query = User::query();
        if ($date) {
            $query->whereDate('createdDateTime', $date);
        }
        $users = $query->paginate(10);
    
        return view('dashboard', compact('users', 'date'));
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        $query = $user->signIns();
    
        // Handle date filtering
        if ($request->has('range')) {
            if ($request->input('range') === 'last_month') {
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
            } else {
                // Default this month
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
            }
        } elseif ($request->has('date')) {
            $start = Carbon::parse($request->input('date'))->startOfDay();
            $end = Carbon::parse($request->input('date'))->endOfDay();
        } else {
            // Default to this month
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
        }
    
        $signIns = $query->whereBetween('date_utc', [$start, $end])
                    ->orderByDesc('date_utc')
                    ->paginate(15)
                    ->withQueryString();
    
        return view('user-details', compact('user', 'signIns', 'start', 'end'));
    }
    
}
