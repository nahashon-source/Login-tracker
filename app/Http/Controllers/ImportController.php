<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\SigninLogsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function importUsers(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
        ], [
            'import_file.required' => 'Please select a CSV file to upload.',
            'import_file.mimetypes' => 'The file must be a CSV or TXT file.',
        ]);

        try {
            $file = $request->file('import_file');

            Log::channel('import')->info('Starting user import...', [
                'path' => $file->getRealPath(),
                'originalName' => $file->getClientOriginalName(),
            ]);

            Excel::import(new UsersImport, $file);

            Log::channel('import')->info('✅ Users imported successfully.');

            return redirect()->route('dashboard')->with('success', '✅ Users imported successfully!');
        } catch (\Throwable $e) {
            Log::channel('import')->error('❌ Users import failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Optional: also log to default channel
            Log::error('❌ Users import failed.', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard')->with('error', '❌ Failed to import users: ' . $e->getMessage());
        }
    }

    
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

        Log::channel('import')->info('Starting sign-in import...', [
            'path' => $file->getRealPath(),
            'originalName' => $file->getClientOriginalName(),
        ]);

        Excel::import(new SigninLogsImport, $file); // ✅ using correct class and variable

        Log::channel('import')->info('✅ Sign-ins imported successfully.');

        return redirect()->route('dashboard')->with('success', '✅ Sign-ins imported successfully!');
    } catch (\Throwable $e) {
        Log::channel('import')->error('❌ Sign-ins import failed.', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        Log::error('❌ Sign-ins import failed.', [
            'error' => $e->getMessage(),
        ]);

        return redirect()->route('dashboard')->with('error', '❌ Failed to import sign-ins: ' . $e->getMessage());
    }
}
    
}
