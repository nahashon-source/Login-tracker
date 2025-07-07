<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        // Validate only the fields you actually have in the database
        $validated = $request->validate([
            'userPrincipalName' => 'required|unique:users,userPrincipalName',
            'displayName'       => 'nullable|string',
            'surname1'          => 'nullable|string',
            'mail1'             => 'nullable|email',
            'givenName1'        => 'nullable|string',
            'userType'          => 'nullable|string',
            'jobTitle'          => 'nullable|string',
            'department'        => 'nullable|string',
            'accountEnabled'    => 'boolean',
            // Add more fields as needed
        ]);

        // Generate UUID for primary key
        $validated['id'] = (string) Str::uuid();

        // Set createdDateTime
        $validated['createdDateTime'] = now();

        User::create($validated);

        return redirect()->route('dashboard')->with('success', 'User added successfully!');
    }

    public function destroy($id)
    {
        // Note: primary key is string 'id' field
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('dashboard')->with('success', 'User deleted successfully!');
    }
}
