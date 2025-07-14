<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SigninLog;
use App\Models\System;

class UpdateSystemMappings extends Command
{
    protected $signature = 'update:system-mappings';
    protected $description = 'Update system mappings for existing signin logs';

    public function handle()
    {
        $this->info('Starting system mapping update...');
        
        $systemMapping = config('systemmap', []);
        $updated = 0;
        $total = 0;
        
        // Get distinct applications that need updating
        $applications = SigninLog::select('application')
            ->whereNotNull('application')
            ->distinct()
            ->pluck('application');
            
        foreach ($applications as $application) {
            $normalizedApp = strtolower(trim($application));
            
            // Find system mapping
            $system = $systemMapping[$normalizedApp] ?? null;
            
            // If no direct mapping, try partial match
            if (!$system) {
                foreach ($systemMapping as $appKey => $systemName) {
                    if (strpos($normalizedApp, $appKey) !== false || strpos($appKey, $normalizedApp) !== false) {
                        $system = $systemName;
                        break;
                    }
                }
            }
            
            // Update if mapping found
            if ($system) {
                $count = SigninLog::where('application', $application)
                    ->where(function($query) use ($system) {
                        $query->whereNull('system')
                              ->orWhere('system', '!=', $system);
                    })
                    ->update(['system' => $system]);
                    
                $updated += $count;
                $total += SigninLog::where('application', $application)->count();
                
                // Auto-create system if it doesn't exist
                System::firstOrCreate(['name' => $system]);
                
                if ($count > 0) {
                    $this->info("Updated {$count} records for '{$application}' -> '{$system}'");
                }
            }
        }
        
        $this->info("Processing complete!");
        $this->info("Total records processed: {$total}");
        $this->info("Records updated: {$updated}");
        
        // Show current application -> system mappings
        $this->info("\nCurrent application mappings:");
        $mappings = SigninLog::select('application', 'system')
            ->whereNotNull('application')
            ->distinct()
            ->get();
            
        foreach ($mappings as $mapping) {
            $this->line("'{$mapping->application}' -> '{$mapping->system}'");
        }
    }
}
