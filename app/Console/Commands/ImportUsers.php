<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Log;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {file}';

    /**
     * The console command description.
     *
     * @var string
     */

     
    protected $description = 'Import users from a CSV file';

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

        $this->info('Importing users from ' . $filePath);

        // Log the file path for debugging
        Log::info('Starting user import from: ' . $filePath);

        try {
            // Perform the import using the UsersImport class
            Excel::import(new UsersImport, $filePath);

            $this->info('Users imported successfully!');
            Log::info('Users imported successfully from: ' . $filePath);
        } catch (\Exception $e) {
            // Log the error and output it in the console
            $this->error('Error during import: ' . $e->getMessage());
            Log::error('Error during user import from ' . $filePath . ': ' . $e->getMessage());
        }
    }
}
