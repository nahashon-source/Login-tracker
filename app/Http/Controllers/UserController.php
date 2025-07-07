<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'userPrincipalName' => 'required|unique:users',
            'displayName' => 'nullable|string',
            'surname' => 'nullable|string',
            'mail' => 'nullable|email',
            'givenName' => 'nullable|string',
            'userType' => 'nullable|string',
            'jobTitle' => 'nullable|string',
            'department' => 'nullable|string',
            'accountEnabled' => 'boolean',
            // Add other fields as needed
        ]);

        User::create($validated + ['createdDateTime' => now()]);

        return redirect()->route('dashboard')->with('success', 'User added successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('dashboard')->with('success', 'User deleted successfully!');
    }
}