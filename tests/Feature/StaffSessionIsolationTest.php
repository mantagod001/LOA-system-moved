<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffSessionIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_switches_the_browser_from_one_staff_account_to_another(): void
    {
        $first = User::factory()->create([
            'role' => UserRole::SasoOfficer,
            'email' => 'saso.session@evsu.edu.ph',
        ]);
        $second = User::factory()->create([
            'role' => UserRole::CampusDirector,
            'email' => 'director.session@evsu.edu.ph',
        ]);

        $this->post(route('login.store'), [
            'email' => $first->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('staff.dashboard'));

        $this->assertAuthenticatedAs($first);

        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('saso.session@evsu.edu.ph');

        $this->post(route('login.store'), [
            'email' => $second->email,
            'password' => 'password',
        ])->assertRedirect(route('staff.dashboard'));

        $this->assertAuthenticatedAs($second);
    }

    public function test_a_failed_login_does_not_keep_the_previous_account(): void
    {
        $first = User::factory()->create(['role' => UserRole::Registrar]);
        $second = User::factory()->create(['role' => UserRole::Guidance]);

        $this->post(route('login.store'), [
            'email' => $first->email,
            'password' => 'password',
        ])->assertRedirect(route('staff.dashboard'));

        $this->post(route('login.store'), [
            'email' => $second->email,
            'password' => 'not-the-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_pages_are_not_stored_in_a_shared_cache(): void
    {
        $home = $this->get(route('home'))->assertOk();
        $this->assertStringContainsString('no-store', (string) $home->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $home->headers->get('Cache-Control'));

        $user = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $dashboard = $this->actingAs($user)
            ->get(route('staff.dashboard'))
            ->assertOk();
        $this->assertStringContainsString('no-store', (string) $dashboard->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $dashboard->headers->get('Cache-Control'));
    }
}
