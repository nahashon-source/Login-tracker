<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SigninLog;

class CleanupDuplicateSignins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signin-logs:cleanup-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate sign-in log entries, keeping the oldest record for each group';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for duplicate sign-in records...');
        
        // Find duplicates
        $duplicates = DB::select('
            SELECT user_id, date_utc, application, ip_address, COUNT(*) as count
            FROM signin_logs 
            GROUP BY user_id, date_utc, application, ip_address 
            HAVING COUNT(*) > 1
        ');
        
        if (empty($duplicates)) {
            $this->info('No duplicate records found!');
            return 0;
        }
        
        $this->warn(sprintf('Found %d groups of duplicate records', count($duplicates)));
        
        if ($this->option('dry-run')) {
            $this->info('DRY RUN - Showing what would be deleted:');
            foreach ($duplicates as $duplicate) {
                $this->line(sprintf(
                    'User: %s, Date: %s, App: %s, IP: %s (%d duplicates)',
                    $duplicate->user_id,
                    $duplicate->date_utc,
                    $duplicate->application,
                    $duplicate->ip_address,
                    $duplicate->count
                ));
            }
            return 0;
        }
        
        if (!$this->confirm('Do you want to proceed with removing duplicates?')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        $deletedCount = 0;
        foreach ($duplicates as $duplicate) {
            // Keep the oldest record (minimum created_at)
            $recordsToDelete = DB::select('
                SELECT created_at FROM signin_logs 
                WHERE user_id = ? AND date_utc = ? AND application = ? AND ip_address = ?
                ORDER BY created_at ASC
                LIMIT 999999 OFFSET 1
            ', [$duplicate->user_id, $duplicate->date_utc, $duplicate->application, $duplicate->ip_address]);
            
            foreach ($recordsToDelete as $record) {
                DB::delete('
                    DELETE FROM signin_logs 
                    WHERE user_id = ? AND date_utc = ? AND application = ? AND ip_address = ? AND created_at = ?
                    LIMIT 1
                ', [
                    $duplicate->user_id, 
                    $duplicate->date_utc, 
                    $duplicate->application, 
                    $duplicate->ip_address,
                    $record->created_at
                ]);
                $deletedCount++;
            }
        }
        
        $this->info(sprintf('Successfully deleted %d duplicate records!', $deletedCount));
        return 0;
    }
}
