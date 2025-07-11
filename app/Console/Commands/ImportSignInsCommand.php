<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SigninLog;
use App\Models\User;
use League\Csv\Reader;
use League\Csv\Exception;
use Carbon\Carbon;

class ImportSignInsCommand extends Command
{
    protected $signature = 'import:signins {file}';
    protected $description = 'Import InteractiveSignIns from a CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("❌ File not found: {$filePath}");
            return Command::FAILURE;
        }

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
        } catch (Exception $e) {
            $this->error("❌ Failed to read CSV file: " . $e->getMessage());
            return Command::FAILURE;
        }

        $records = $csv->getRecords();
        $importedCount = 0;

        foreach ($records as $rawRecord) {
            $record = $this->normalizeRecord($rawRecord);

            // Debug: show actual keys

            $user = User::where('userPrincipalName', $record['username'])->first();

            if (!$user) {
                $this->warn("No matching user for username: {$record['username']}. Skipping.");
                continue;
            }

            SigninLog::create([
                'user_id' => $user->id,
                'username' => $record['username'],
                'date_utc' => Carbon::parse($record['date_utc']),
                'system' => $record['system'] ?? 'SCM',
                'resource_display_name' => $record['resource_display_name'] ?? null,
            ]);

            $importedCount++;
        }

        $this->info("✅ Imported {$importedCount} sign-ins.");
        return Command::SUCCESS;
    }

    protected function normalizeRecord(array $record): array
    {
        $normalized = [];
        foreach ($record as $key => $value) {
            $cleanKey = strtolower(str_replace(' ', '_', trim($key)));
            $normalized[$cleanKey] = trim($value);
        }
        return $normalized;
    }
}
