<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExtractUniqueSystems extends Command
{
    protected $signature = 'csv:extract-systems';
    protected $description = 'Extract unique system values from signin_logs.csv';

    public function handle()
    {
        $filePath = storage_path('app/public/signin_logs.csv');
        $systems = [];

        if (!file_exists($filePath)) {
            $this->error('CSV file not found at: ' . $filePath);
            return 1;
        }

        $file = new \SplFileObject($filePath);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl(',');

        // Read header to find 'system' column index
        $header = $file->fgetcsv();
        $systemIndex = array_search('system', $header);

        if ($systemIndex === false) {
            $this->error('System column not found in CSV header');
            return 1;
        }

        // Read data rows
        while (!$file->eof() && ($row = $file->fgetcsv()) !== false) {
            if (isset($row[$systemIndex]) && !empty($row[$systemIndex])) {
                $systems[$row[$systemIndex]] = true; // Use array key to ensure uniqueness
            }
        }

        // Output unique systems
        foreach (array_keys($systems) as $system) {
            $this->line($system);
        }

        $this->info('Found ' . count($systems) . ' unique system values.');
        return 0;
    }
}