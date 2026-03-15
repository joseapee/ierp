<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class RegisterWizard extends Component
{
    public int $step = 1;

    public int $totalSteps = 2;

    // Step 1: Auth info
    public string $email = '';

    public string $password = '';

    // Step 2: Personal info
    public string $name = '';

    public string $phone = '';

    public function nextStep(): void
    {
        $this->validateStep();
        $this->step = min($this->step + 1, $this->totalSteps);
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function completeRegistration(): void
    {
        $this->validateStep();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'phone' => $this->phone ?: null,
        ]);

        event(new Registered($user));
        Auth::login($user);

        $this->redirect(route('setup'));
    }

    protected function validateStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', Password::defaults()],
            ]),
            2 => $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
            ]),
            default => null,
        };
    }

    public function render(): mixed
    {
        return view('livewire.auth.register-wizard');
    }
}
