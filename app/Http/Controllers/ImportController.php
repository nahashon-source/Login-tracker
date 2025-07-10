<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\InteractiveSignInsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    /**
     * Import users from CSV/TXT file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importUsers(Request $request)
    {
        try {
            // Validate file exists and is CSV or TXT
            $request->validate([
                'import_file' => 'required|mimes:csv,txt',
            ]);

            $file = $request->file('import_file');

            // Log file details for debugging
            Log::info('Users Import - Uploaded File Path: ' . $file->getRealPath());

            // Perform the import
            Excel::import(new UsersImport, $file);

            // Redirect to dashboard with success message
            return redirect()->route('dashboard')->with('success', 'Users imported successfully!');
        } catch (\Exception $e) {
            // Log error details for troubleshooting
            Log::error('Users Import Failed: ' . $e->getMessage());

            // Redirect back to the import page with error message
            return redirect()->back()->with('error', 'Failed to import users. Please try again.');
        }
    }

    /**
     * Import sign-ins from CSV/TXT file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importSignIns(Request $request)
    {
        try {
            // Validate file exists and is CSV or TXT
            $request->validate([
                'import_file' => 'required|mimes:csv,txt',
            ]);

            $file = $request->file('import_file');

            // Log file details for debugging
            Log::info('Sign-Ins Import - Uploaded File Path: ' . $file->getRealPath());

            // Perform the import
            Excel::import(new InteractiveSignInsImport, $file);

            // Redirect to dashboard with success message
            return redirect()->route('dashboard')->with('success', 'Sign-ins imported successfully!');
        } catch (\Exception $e) {
            // Log error details for troubleshooting
            Log::error('Sign-Ins Import Failed: ' . $e->getMessage());

            // Redirect back to the import page with error message
            return redirect()->back()->with('error', 'Failed to import sign-ins. Please try again.');
        }
    }
}
