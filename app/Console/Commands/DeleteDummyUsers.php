<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DeleteDummyUsers extends Command
{
    protected $signature = 'cleanup:delete-dummy-users';

    protected $description = 'Delete users with userPrincipalName containing example.com';

    public function handle()
    {
        $count = User::where('userPrincipalName', 'like', '%example.com%')->count();

        if ($count === 0) {
            $this->info('✅ No dummy users found.');
            return 0;
        }

        if ($this->confirm("⚠️  {$count} dummy users found. Do you want to delete them?")) {
            User::where('userPrincipalName', 'like', '%example.com%')->delete();
            $this->info("✅ {$count} dummy users deleted successfully.");
        } else {
            $this->info('❌ Operation cancelled. No users deleted.');
        }

        return 0;
    }
}
