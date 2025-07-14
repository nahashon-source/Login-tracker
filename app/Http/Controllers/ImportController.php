<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\SigninLogsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Services\ImportLockService;
use App\Models\User;
use App\Models\System;
use League\Csv\Reader;

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

        $lockService = new ImportLockService();
        
        // Check if another import is in progress
        if ($lockService->isLocked()) {
            return redirect()->back()->with('error', 'Another import is already in progress. Please wait and try again.');
        }

        // Acquire lock
        if (!$lockService->acquireLock()) {
            return redirect()->back()->with('error', 'Unable to start import. Please try again.');
        }

        try {
            $file = $request->file('import_file');

            Log::channel('import')->info('Starting user import...', [
                'path' => $file->getRealPath(),
                'originalName' => $file->getClientOriginalName(),
            ]);

            DB::transaction(function () use ($file) {
                Excel::import(new UsersImport, $file);
            });

            Log::channel('import')->info('✅ Users imported successfully.');

            $lockService->releaseLock();
            return redirect()->route('dashboard')->with('success', '✅ Users imported successfully!');
        } catch (\Throwable $e) {
            Log::channel('import')->error('❌ Users import failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Log::error('❌ Users import failed.', [
                'error' => $e->getMessage(),
            ]);

            $lockService->releaseLock();
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

        $lockService = new ImportLockService();
        
        // Check if another import is in progress
        if ($lockService->isLocked()) {
            return redirect()->back()->with('error', 'Another import is already in progress. Please wait and try again.');
        }

        // Acquire lock
        if (!$lockService->acquireLock()) {
            return redirect()->back()->with('error', 'Unable to start import. Please try again.');
        }

        try {
            $file = $request->file('import_file');

            Log::channel('import')->info('Starting sign-in import...', [
                'path' => $file->getRealPath(),
                'originalName' => $file->getClientOriginalName(),
            ]);

            DB::transaction(function () use ($file) {
                Excel::import(new SigninLogsImport, $file);
            });

            // Update user login status based on the newly imported sign-in data
            \Artisan::call('users:update-login-status');
            
            Log::channel('import')->info('✅ Sign-ins imported successfully and user login status updated.');

            $lockService->releaseLock();
            return redirect()->route('dashboard')->with('success', '✅ Sign-ins imported successfully and user login status updated!');
        } catch (\Throwable $e) {
            Log::channel('import')->error('❌ Sign-ins import failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Log::error('❌ Sign-ins import failed.', [
                'error' => $e->getMessage(),
            ]);

            $lockService->releaseLock();
            return redirect()->route('dashboard')->with('error', '❌ Failed to import sign-ins: ' . $e->getMessage());
        }
    }

}
