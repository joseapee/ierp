<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function mockSocialiteUser(string $id = '123456', string $email = 'social@example.com', string $name = 'Social User', ?string $avatar = null): SocialiteUser
    {
        $user = Mockery::mock(SocialiteUser::class);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('getEmail')->andReturn($email);
        $user->shouldReceive('getName')->andReturn($name);
        $user->shouldReceive('getNickname')->andReturn($name);
        $user->shouldReceive('getAvatar')->andReturn($avatar);

        return $user;
    }

    public function test_social_redirect_to_google(): void
    {
        $response = $this->get(route('social.redirect', 'google'));

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location') ?? '');
    }

    public function test_social_redirect_to_apple(): void
    {
        $response = $this->get(route('social.redirect', 'apple'));

        $response->assertRedirect();
        $this->assertStringContainsString('appleid.apple.com', $response->headers->get('Location') ?? '');
    }

    public function test_invalid_provider_returns_404(): void
    {
        $this->get(route('social.redirect', 'invalid'))
            ->assertNotFound();
    }

    public function test_google_callback_creates_new_user(): void
    {
        $socialUser = $this->mockSocialiteUser('google-123', 'newuser@example.com', 'New Google User');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock(\Laravel\Socialite\Contracts\Provider::class, function ($mock) use ($socialUser) {
                $mock->shouldReceive('user')->andReturn($socialUser);
            }));

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('setup'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New Google User',
            'social_provider' => 'google',
            'social_id' => 'google-123',
        ]);

        $this->assertAuthenticated();
    }

    public function test_apple_callback_creates_new_user(): void
    {
        $socialUser = $this->mockSocialiteUser('apple-456', 'appleuser@example.com', 'Apple User');

        Socialite::shouldReceive('driver')
            ->with('apple')
            ->andReturn(Mockery::mock(\Laravel\Socialite\Contracts\Provider::class, function ($mock) use ($socialUser) {
                $mock->shouldReceive('user')->andReturn($socialUser);
            }));

        $this->get(route('social.callback', 'apple'))
            ->assertRedirect(route('setup'));

        $this->assertDatabaseHas('users', [
            'email' => 'appleuser@example.com',
            'social_provider' => 'apple',
            'social_id' => 'apple-456',
        ]);

        $this->assertAuthenticated();
    }

    public function test_callback_logs_in_existing_social_user(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'social_provider' => 'google',
            'social_id' => 'existing-123',
            'tenant_id' => null,
        ]);

        $socialUser = $this->mockSocialiteUser('existing-123', 'existing@example.com', 'Existing User');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock(\Laravel\Socialite\Contracts\Provider::class, function ($mock) use ($socialUser) {
                $mock->shouldReceive('user')->andReturn($socialUser);
            }));

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('setup'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_callback_links_social_account_to_existing_email_user(): void
    {
        $user = User::factory()->create([
            'email' => 'link@example.com',
            'social_provider' => null,
            'social_id' => null,
        ]);

        $socialUser = $this->mockSocialiteUser('link-789', 'link@example.com', 'Link User');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock(\Laravel\Socialite\Contracts\Provider::class, function ($mock) use ($socialUser) {
                $mock->shouldReceive('user')->andReturn($socialUser);
            }));

        $this->get(route('social.callback', 'google'));

        $user->refresh();
        $this->assertEquals('google', $user->social_provider);
        $this->assertEquals('link-789', $user->social_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_social_user_email_is_verified_automatically(): void
    {
        $socialUser = $this->mockSocialiteUser('verify-123', 'verify@example.com', 'Verify User');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock(\Laravel\Socialite\Contracts\Provider::class, function ($mock) use ($socialUser) {
                $mock->shouldReceive('user')->andReturn($socialUser);
            }));

        $this->get(route('social.callback', 'google'));

        $user = User::where('email', 'verify@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_callback_handles_provider_failure_gracefully(): void
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock(\Laravel\Socialite\Contracts\Provider::class, function ($mock) {
                $mock->shouldReceive('user')->andThrow(new \Exception('OAuth error'));
            }));

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
    }

    public function test_register_page_shows_social_buttons(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Google')
            ->assertSee('Apple');
    }
}
