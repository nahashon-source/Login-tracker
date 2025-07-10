<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportNoSignins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:no-signins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export users who have no sign-ins to a CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Fetching users without sign-ins...');

        // Fetch userPrincipalNames without any sign-ins
        $noSignins = DB::table('users')
            ->whereNotIn('id', function ($q) {
                $q->select('user_id')->from('interactive_sign_ins');
            })
            ->orderBy('userPrincipalName')
            ->pluck('userPrincipalName');

        if ($noSignins->isEmpty()) {
            $this->warn('✅ All users have sign-ins. Nothing to export.');
            return;
        }

        // Compose CSV content
        $csvContent = "userPrincipalName\n" . $noSignins->implode("\n");

        // Save to public disk
        $filePath = 'no_signins.csv';
        Storage::disk('public')->put($filePath, $csvContent);

        $this->info("✅ Exported {$noSignins->count()} users to storage/app/public/{$filePath}");
    }
}
