<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InteractiveSignInsImport;

class ImportInteractiveSignIns extends Command
{
    protected $signature = 'import:interactiveSignIns {file}';

    protected $description = 'Import interactive sign-ins from a CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1;
        }

        try {
            Excel::import(new InteractiveSignInsImport, $filePath);
            $this->info('Interactive sign-ins imported successfully.');
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
        }

        return 0;
    }
}
