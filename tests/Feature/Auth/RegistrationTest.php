<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account');
    }

    public function test_register_page_shows_social_buttons(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Google')
            ->assertSee('Apple');
    }

    public function test_register_wizard_has_two_steps(): void
    {
        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->assertSet('step', 1)
            ->assertSet('totalSteps', 2);
    }

    public function test_step_one_validates_email_and_password(): void
    {
        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', '')
            ->set('password', '')
            ->call('nextStep')
            ->assertHasErrors(['email', 'password']);
    }

    public function test_step_one_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', 'taken@example.com')
            ->set('password', 'password123!')
            ->call('nextStep')
            ->assertHasErrors(['email']);
    }

    public function test_step_one_advances_to_step_two(): void
    {
        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', 'new@example.com')
            ->set('password', 'password123!')
            ->call('nextStep')
            ->assertSet('step', 2);
    }

    public function test_step_two_validates_name_required(): void
    {
        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', 'new@example.com')
            ->set('password', 'password123!')
            ->call('nextStep')
            ->set('name', '')
            ->call('completeRegistration')
            ->assertHasErrors(['name']);
    }

    public function test_new_users_can_register(): void
    {
        Event::fake([Registered::class]);

        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', 'newuser@example.com')
            ->set('password', 'password123!')
            ->call('nextStep')
            ->set('name', 'Test User')
            ->set('phone', '+234 800 000 0000')
            ->call('completeRegistration')
            ->assertRedirect(route('setup'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'Test User',
            'phone' => '+234 800 000 0000',
        ]);

        $this->assertAuthenticated();
        Event::assertDispatched(Registered::class);
    }

    public function test_new_user_email_is_not_verified(): void
    {
        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', 'unverified@example.com')
            ->set('password', 'password123!')
            ->call('nextStep')
            ->set('name', 'Unverified User')
            ->call('completeRegistration');

        $user = User::where('email', 'unverified@example.com')->first();
        $this->assertNull($user->email_verified_at);
    }

    public function test_unverified_user_is_redirected_to_email_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('setup'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_setup(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('setup'))
            ->assertOk();
    }

    public function test_registration_sends_verification_email(): void
    {
        Notification::fake();

        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', 'verify-test@example.com')
            ->set('password', 'password123!')
            ->call('nextStep')
            ->set('name', 'Verify Test User')
            ->call('completeRegistration');

        $user = User::where('email', 'verify-test@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_previous_step_goes_back(): void
    {
        Livewire::test(\App\Livewire\Auth\RegisterWizard::class)
            ->set('email', 'new@example.com')
            ->set('password', 'password123!')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->call('previousStep')
            ->assertSet('step', 1);
    }

    public function test_login_page_has_create_account_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Create Account');
    }
}
