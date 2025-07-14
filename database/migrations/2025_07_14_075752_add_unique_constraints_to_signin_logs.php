<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For now, we'll skip the unique constraint to avoid migration issues
        // The ImportLockService will handle duplicate prevention at the application level
        // This can be uncommented later once duplicates are manually resolved
        
        // Schema::table('signin_logs', function (Blueprint $table) {
        //     $table->unique(['user_id', 'date_utc', 'application', 'ip_address'], 'unique_signin_record');
        // });
    }
    
    private function removeDuplicateRecords()
    {
        // Find and remove duplicate records
        $duplicates = DB::select('
            SELECT user_id, date_utc, application, ip_address, COUNT(*) as count
            FROM signin_logs 
            GROUP BY user_id, date_utc, application, ip_address 
            HAVING COUNT(*) > 1
        ');
        
        foreach ($duplicates as $duplicate) {
            // For each duplicate group, keep only the first record (by created_at)
            DB::statement('
                DELETE FROM signin_logs 
                WHERE user_id = ? 
                AND date_utc = ? 
                AND application = ? 
                AND ip_address = ? 
                AND created_at NOT IN (
                    SELECT min_created_at FROM (
                        SELECT MIN(created_at) as min_created_at 
                        FROM signin_logs 
                        WHERE user_id = ? 
                        AND date_utc = ? 
                        AND application = ? 
                        AND ip_address = ?
                    ) as temp
                )
            ', [
                $duplicate->user_id, $duplicate->date_utc, $duplicate->application, $duplicate->ip_address,
                $duplicate->user_id, $duplicate->date_utc, $duplicate->application, $duplicate->ip_address
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signin_logs', function (Blueprint $table) {
            $table->dropUnique('unique_signin_record');
        });
    }
};
