<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\InteractiveSignInsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        Excel::import(new UsersImport, $request->file('file'));
        return redirect()->back()->with('success', 'Users imported successfully!');
    }

    public function importSignIns(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        Excel::import(new InteractiveSignInsImport, $request->file('file'));
        return redirect()->back()->with('success', 'Sign-ins imported successfully!');
    }
}