<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Storage;

class ImportUsers extends Command
{
    protected $signature = 'import:users {file}';
    protected $description = 'Import users from a CSV file';

    public function handle()
    {
        $file = $this->argument('file');
        $filePath = storage_path('app/public/' . $file);

        if (!file_exists($filePath)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Starting import of users from: {$filePath}");

        try {
            Excel::import(new UsersImport, $filePath);
            $this->info('✅ User import complete!');
        } catch (\Exception $e) {
            $this->error("❌ Import failed: " . $e->getMessage());
        }

        return 0;
    }
}
