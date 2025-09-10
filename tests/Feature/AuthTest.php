<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_protected_pages_require_auth(): void
    {
        $this->get('/operators')->assertRedirect('/login');
        $this->get('/absences')->assertRedirect('/login');
        $this->get('/post-status')->assertRedirect('/login');
    }

    public function test_logged_user_can_view_pages(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/operators')->assertOk();
        $this->get('/absences')->assertOk();
        $this->get('/post-status')->assertOk();
    }
} 