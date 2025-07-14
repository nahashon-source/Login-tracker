<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SigninLog;
use Illuminate\Support\Facades\DB;

class UpdateUserLoginStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-login-status {--days=30 : Number of days to check for recent logins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user login status based on recent sign-in activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Updating user login status based on activity in the last {$days} days...");

        // Get the date threshold
        $dateThreshold = now()->subDays($days);

        // Find users with recent successful sign-ins
        $usersWithRecentLogins = DB::table('users')
            ->whereExists(function ($query) use ($dateThreshold) {
                $query->select(DB::raw(1))
                      ->from('signin_logs')
                      ->whereColumn('signin_logs.user_id', 'users.id')
                      ->where('signin_logs.status', 'Success')
                      ->where('signin_logs.date_utc', '>=', $dateThreshold);
            })
            ->pluck('id');

        $this->info("Found {$usersWithRecentLogins->count()} users with recent successful logins.");

        // Update the logged_in status
        $updated = User::whereIn('id', $usersWithRecentLogins)
                      ->update(['logged_in' => true]);

        $this->info("Updated {$updated} users to logged_in = true.");

        // Set users without recent logins to logged_in = false
        $notLoggedIn = User::whereNotIn('id', $usersWithRecentLogins)
                          ->update(['logged_in' => false]);

        $this->info("Updated {$notLoggedIn} users to logged_in = false.");

        $this->info('✅ User login status update completed!');
        
        return Command::SUCCESS;
    }
}
