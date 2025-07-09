<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportSignInsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_signins_can_be_imported_from_csv()
    {
        // Arrange: create a user with a known userPrincipalName
        $user = User::factory()->create([
            'userPrincipalName' => 'test@example.com'
        ]);

        // Fake the local storage for isolation
        Storage::fake('local');

        // Prepare CSV content
        $csvContent = implode("\n", [
            "username,date_utc,system,resource_display_name",
            "test@example.com,2025-07-04 12:00:00,SCM,FITerp UAT"
        ]) . "\n";

        // Store the CSV content into fake storage
        Storage::disk('local')->put('test_signins.csv', $csvContent);

        // Resolve the absolute path to the fake file
        $csvPath = Storage::disk('local')->path('test_signins.csv');

        // Act: run the Artisan command
        $exitCode = Artisan::call('import:signins', ['file' => $csvPath]);

        // Assert: record exists in database
        $this->assertDatabaseHas('interactive_sign_ins', [
            'username'              => 'test@example.com',
            'system'                => 'SCM',
            'resource_display_name' => 'FITerp UAT',
        ]);

        // Optional: Assert Artisan command exited successfully
        $this->assertEquals(0, $exitCode);
    }
}
