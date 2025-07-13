<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\System;
use Illuminate\Support\Facades\DB;

class PopulateApplications extends Command
{
    protected $signature = 'populate:applications';
    protected $description = 'Populate application assignments for testing';

    public function handle()
    {
        $assignments = [
            ['juma.k@freight-in-time.com', 'SCM'],
            ['precious.n@freight-in-time.com', 'SCM'],
            ['nejat.u@freight-in-time.com', 'FIT ERP'],
            ['L.Mlingi@freight-in-time.com', 'FIT ERP'],
            ['i.kisitu@freight-in-time.com', 'Fit Express UAT'],
            ['g.munene@freight-in-time.com', 'FITerp UAT'],
            ['mark@freight-in-time.com', 'OPS'],
            ['d.atsango@freight-in-time.com', 'OPS UAT'],
            ['Belayneh.a@freight-in-time.com', 'SCM'],
            ['bashir.k@freight-in-time.com', 'FIT ERP'],
        ];

        foreach ($assignments as $assignment) {
            $user = User::where('userPrincipalName', $assignment[0])->first();
            $system = System::where('name', $assignment[1])->first();
            
            if ($user && $system) {
                $exists = DB::table('application_user')
                    ->where('user_id', $user->id)
                    ->where('application_id', $system->id)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('application_user')->insert([
                        'user_id' => $user->id,
                        'application_id' => $system->id,
                    ]);
                    $this->info("Added: {$user->userPrincipalName} to {$system->name}");
                } else {
                    $this->line("Already exists: {$user->userPrincipalName} to {$system->name}");
                }
            } else {
                $this->warn("User or system not found: {$assignment[0]} -> {$assignment[1]}");
            }
        }

        $this->info('Application assignments populated successfully!');
        return Command::SUCCESS;
    }
}
