<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportUsersCommand extends Command
{
    protected $signature = 'import:users {file : The path to the CSV file}';

    protected $description = 'Import users from a CSV file into the database';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        try {
            Excel::import(new UsersImport, $filePath);
            $this->info('Users imported successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error importing users: ' . $e->getMessage());
            return 1;
        }
    }
}
