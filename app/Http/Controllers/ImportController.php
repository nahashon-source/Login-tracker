<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\InteractiveSignInsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

/**
 * Controller responsible for handling CSV import operations
 * for users and interactive sign-ins using Laravel Excel.
 */
class ImportController extends Controller
{
    /**
     * Handle the import of user records from a CSV or TXT file.
     *
     * Validates the uploaded file's MIME type and processes it using UsersImport.
     * On success, redirects back with a success message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importUsers(Request $request)
    {
        // Validate the incoming request to ensure a file was uploaded
        // and that it's either a CSV or TXT file.
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        // Import users using the UsersImport class via Laravel Excel
        Excel::import(new UsersImport, $request->file('file'));

        // Redirect back with a success flash message
        return redirect()->back()->with('success', 'Users imported successfully!');
    }

    /**
     * Handle the import of interactive sign-in records from a CSV or TXT file.
     *
     * Validates the uploaded file's MIME type and processes it using InteractiveSignInsImport.
     * On success, redirects back with a success message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importSignIns(Request $request)
    {
        // Validate that a file was uploaded and check its MIME type
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        // Import sign-ins using the InteractiveSignInsImport class via Laravel Excel
        Excel::import(new InteractiveSignInsImport, $request->file('file'));

        // Redirect back with a success flash message
        return redirect()->back()->with('success', 'Sign-ins imported successfully!');
    }
}
