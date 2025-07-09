<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class UserImportCommand extends Command
{
    protected $signature = 'user:import {--file=}';

    protected $description = 'Import users from a CSV file';

    public function handle()
    {
        $file = $this->option('file');

        if (!$file) {
            $this->error('Please provide a CSV file path using --file=');
            return 1;
        }

        $this->info("Starting import from {$file}...");

        Excel::import(new UsersImport, storage_path("app/{$file}"));

        $this->info('✅ User import completed successfully.');
    }
}
