<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create one specific test user
        // User::factory()->create([
        //     'userPrincipalName' => 'test@example.com',
        //     'displayName' => 'Test User',
        //     'surname1' => 'Doe',
        //     'surname2' => 'Smith',
        //     'mail1' => 'primary@example.com',
        //     'mail2' => 'secondary@example.com',
        //     'givenName1' => 'John',
        //     'givenName2' => 'Edward',
        //     'userType' => 'member',
        //     'jobTitle' => 'Developer',
        //     'department' => 'IT',
        //     'accountEnabled' => true,
        //     'createdDateTime' => now(),
        // ]);

        // Optionally — seed 10 more random users
        // User::factory(10)->create();
    }
}
