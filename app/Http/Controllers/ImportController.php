<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\SigninImport;
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
        $request->validate([
            'import_file' => 'required|mimes:csv,txt',
        ], [
            'import_file.required' => 'Please select a CSV file to upload.',
            'import_file.mimes' => 'The file must be a CSV or TXT file.',
        ]);

        try {
            $file = $request->file('import_file');

            Log::info('Users Import - Uploaded File Path: ' . $file->getRealPath());

            Excel::import(new UsersImport, $file);

            return redirect()->route('dashboard')
                ->with('success', '✅ Users imported successfully!');

        } catch (\Exception $e) {
            Log::error('Users Import Failed: ' . $e->getMessage());

            return redirect()->route('imports.index')
                ->with('error', '❌ Failed to import users. Please check your file and try again.');
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
        $request->validate([
            'import_file' => 'required|mimes:csv,txt',
        ], [
            'import_file.required' => 'Please select a CSV file to upload.',
            'import_file.mimes' => 'The file must be a CSV or TXT file.',
        ]);

        try {
            $file = $request->file('import_file');

            Log::info('Sign-Ins Import - Uploaded File Path: ' . $file->getRealPath());

            Excel::import(new SigninImport, $file);

            return redirect()->route('dashboard')
                ->with('success', '✅ Sign-ins imported successfully!');

        } catch (\Exception $e) {
            Log::error('Sign-Ins Import Failed: ' . $e->getMessage());

            return redirect()->route('imports.index')
                ->with('error', '❌ Failed to import sign-ins. Please check your file and try again.');
        }
    }
}
