<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SigninLogsImport;
use Illuminate\Support\Facades\Log;

class ImportSigninLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:signin-logs {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import signin logs from a CSV file using SigninLogsImport';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get the file path from the argument
        $filePath = $this->argument('file');

        // Check if the file exists
        if (!file_exists($filePath)) {
            $this->error("The file {$filePath} does not exist.");
            return;
        }

        $this->info('Importing signin logs from ' . $filePath);

        // Log the file path for debugging
        Log::info('Starting signin logs import from: ' . $filePath);

        try {
            // Perform the import using the SigninLogsImport class
            Excel::import(new SigninLogsImport, $filePath);

            $this->info('Signin logs imported successfully!');
            Log::info('Signin logs imported successfully from: ' . $filePath);
        } catch (\Exception $e) {
            // Log the error and output it in the console
            $this->error('Error during import: ' . $e->getMessage());
            Log::error('Error during signin logs import from ' . $filePath . ': ' . $e->getMessage());
        }
    }
}
