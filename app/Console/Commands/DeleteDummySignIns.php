<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InteractiveSignIn;
use App\Models\User;

class DeleteDummySignIns extends Command
{
    protected $signature = 'cleanup:delete-dummy-signins';

    protected $description = 'Delete sign-ins associated with dummy users containing example.com';

    public function handle()
    {
        // Find dummy user IDs
        $dummyUserIds = User::where('userPrincipalName', 'like', '%example.com%')->pluck('id');

        if ($dummyUserIds->isEmpty()) {
            $this->info('✅ No dummy users found — no sign-ins to delete.');
            return 0;
        }

        $count = InteractiveSignIn::whereIn('user_id', $dummyUserIds)->count();

        if ($count === 0) {
            $this->info('✅ No sign-ins associated with dummy users found.');
            return 0;
        }

        if ($this->confirm("⚠️  {$count} sign-ins found for dummy users. Do you want to delete them?")) {
            InteractiveSignIn::whereIn('user_id', $dummyUserIds)->delete();
            $this->info("✅ {$count} sign-ins deleted successfully.");
        } else {
            $this->info('❌ Operation cancelled. No sign-ins deleted.');
        }

        return 0;
    }
}
