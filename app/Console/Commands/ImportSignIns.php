<?php 


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InteractiveSignInsImport;
use Illuminate\Support\Facades\Log;

class ImportSignIns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:sign-ins {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import sign-ins from a CSV file';

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

        $this->info('Importing sign-ins from ' . $filePath);

        // Log the file path for debugging
        Log::info('Starting sign-in import from: ' . $filePath);

        try {
            // Perform the import using the InteractiveSignInsImport class
            Excel::import(new InteractiveSignInsImport, $filePath);

            $this->info('Sign-ins imported successfully!');
            Log::info('Sign-ins imported successfully from: ' . $filePath);
        } catch (\Exception $e) {
            // Log the error and output it in the console
            $this->error('Error during import: ' . $e->getMessage());
            Log::error('Error during sign-in import from ' . $filePath . ': ' . $e->getMessage());
        }
    }
}
