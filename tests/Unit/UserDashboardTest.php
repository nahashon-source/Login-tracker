<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\SigninLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_users_on_dashboard_with_login_counts()
    {
        $user1 = User::factory()->create([
            'userPrincipalName' => 'test1@example.com',
            'displayName' => 'Test User 1',
        ]);

        $user2 = User::factory()->create([
            'userPrincipalName' => 'test2@example.com',
            'displayName' => 'Test User 2',
        ]);

        // Add 2 sign-ins for user1 under system SCM
        SigninLog::factory()->create([
            'username' => 'test1@example.com',
            'date_utc' => Carbon::now()->subDays(1),
            'system' => 'SCM',
        ]);

        SigninLog::factory()->create([
            'username' => 'test1@example.com',
            'date_utc' => Carbon::now()->subDays(2),
            'system' => 'SCM',
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Test User 1');
        $response->assertSee('Test User 2');
        $response->assertSee('Logged In Users');
        $response->assertSee('Not Logged In Users');
        $response->assertSee('Count: 2');
        $response->assertSee('Count: 0');
    }
}
