<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InteractiveSignInsImport;

class ImportInteractiveSignIns extends Command
{
    protected $signature = 'import:interactive-signins';
protected $description = 'Import Interactive Sign-Ins from CSV file';

public function handle()
{
    $this->info("✅ Starting Interactive Sign-Ins CSV import...");

    $filePath = storage_path('app/public/interactive_sign_ins.csv');

    if (!file_exists($filePath)) {
        $this->error("❌ File not found: {$filePath}");
        return 1;
    }

    $this->info("✅ Found file: {$filePath}");

    try {
        Excel::import(new InteractiveSignInsImport, $filePath);
        $this->info("✅ Import completed successfully.");
    } catch (\Exception $e) {
        $this->error("❌ Import failed: " . $e->getMessage());
        return 1;
    }

    return 0;
}

}
