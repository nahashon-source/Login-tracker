<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\System;
use Illuminate\Support\Facades\DB;

class TestApplicationAssignments extends Command
{
    protected $signature = 'test:applications';
    protected $description = 'Test and display application assignments for users';

    public function handle()
    {
        $this->info('Testing Application Assignments...');
        
        // Show all systems
        $systems = System::all();
        $this->info('Available Systems:');
        foreach ($systems as $system) {
            $this->line("- {$system->name} (ID: {$system->id})");
        }
        
        $this->newLine();
        
        // Show users with their applications
        $users = User::with('systems')->take(10)->get();
        
        $this->info('Users and their Applications:');
        foreach ($users as $user) {
            $applications = $user->systems->pluck('name')->join(', ');
            $this->line("{$user->displayName} ({$user->userPrincipalName}): {$applications}");
        }
        
        $this->newLine();
        
        // Show application-user relationships count
        $relationshipCount = DB::table('application_user')->count();
        $this->info("Total application-user relationships: {$relationshipCount}");
        
        $this->newLine();
        
        // Show users by system
        foreach ($systems as $system) {
            $userCount = $system->users()->count();
            $this->line("Users in {$system->name}: {$userCount}");
        }
        
        return Command::SUCCESS;
    }
}
