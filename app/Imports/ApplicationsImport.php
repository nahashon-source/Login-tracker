<?php

namespace App\Imports;

use App\Models\User;
use App\Models\System;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class ApplicationsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $row = array_change_key_case($row, CASE_LOWER);

        // Skip if no UPN or system
        if (empty($row['userprincipalname']) || empty($row['system'])) {
            Log::info('Skipped application import due to missing data', ['row' => $row]);
            return null;
        }

        // Find the user by UPN
        $user = User::where('userPrincipalName', $row['userprincipalname'])->first();
        if (!$user) {
            Log::warning('User not found for application import', ['userPrincipalName' => $row['userprincipalname']]);
            return null;
        }

        // Find the system by name
        $system = System::where('name', $row['system'])->first();
        if (!$system) {
            Log::warning('System not found for application import', ['system' => $row['system']]);
            return null;
        }

        // Check if the relationship already exists
        $existingRelation = DB::table('application_user')
            ->where('user_id', $user->id)
            ->where('application_id', $system->id)
            ->exists();

        if ($existingRelation) {
            Log::info('Application-user relationship already exists', [
                'user_id' => $user->id,
                'application_id' => $system->id
            ]);
            return null;
        }

        // Create the relationship
        DB::table('application_user')->insert([
            'user_id' => $user->id,
            'application_id' => $system->id,
        ]);

        Log::info('Application-user relationship created', [
            'user_id' => $user->id,
            'application_id' => $system->id,
            'user_name' => $user->userPrincipalName,
            'system_name' => $system->name
        ]);

        return null; // We're handling the insert manually
    }
}
