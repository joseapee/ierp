<?php

declare(strict_types=1);

namespace App\Livewire\UserManagement;

use App\Http\Requests\UserManagement\StoreUserRequest;
use App\Http\Requests\UserManagement\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Notifications\UserCredentialsNotification;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserFormModal extends Component
{
    public bool $showModal = false;

    public bool $showCredentialsModal = false;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $phone = '';

    public bool $is_active = true;

    public array $role_ids = [];

    /** @var array{name: string, email: string, password: string}|null */
    public ?array $createdCredentials = null;

    protected $listeners = [
        'openUserFormModal' => 'open',
    ];

    public function open(?int $userId = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'phone', 'is_active', 'role_ids', 'createdCredentials', 'showCredentialsModal']);
        $this->userId = $userId;
        $this->is_active = true;

        if ($userId) {
            $user = User::with('roles')->findOrFail($userId);
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->is_active = $user->is_active;
            $this->role_ids = $user->roles->pluck('id')->toArray();
        }

        $this->showModal = true;
    }

    public function generatePassword(): void
    {
        $generated = Str::password(16);
        $this->password = $generated;
        $this->password_confirmation = $generated;
    }

    public function save(): void
    {
        $service = app(UserService::class);

        if ($this->userId) {
            $rules = (new UpdateUserRequest)->rules();
            $rules['email'][3] = Rule::unique('users', 'email')->ignore($this->userId);
            $validated = $this->validate($rules);
            $user = User::findOrFail($this->userId);
            $this->authorize('update', $user);
            $service->update($user, array_merge($validated, ['role_ids' => $this->role_ids]));
            $this->dispatch('toast', message: 'User updated successfully.', type: 'success');
            $this->showModal = false;
            $this->dispatch('userSaved');
        } else {
            $plainPassword = $this->password;
            $validated = $this->validate((new StoreUserRequest)->rules());
            $this->authorize('create', User::class);
            $user = $service->create(array_merge($validated, ['role_ids' => $this->role_ids]));

            $this->createdCredentials = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $plainPassword,
            ];

            $this->showModal = false;
            $this->showCredentialsModal = true;
            $this->dispatch('toast', message: 'User created successfully.', type: 'success');
        }
    }

    public function emailCredentials(): void
    {
        if (! $this->createdCredentials) {
            return;
        }

        $user = User::where('email', $this->createdCredentials['email'])->firstOrFail();
        $loginUrl = url('/login');

        $user->notify(new UserCredentialsNotification(
            plainPassword: $this->createdCredentials['password'],
            loginUrl: $loginUrl,
        ));

        $this->dispatch('toast', message: 'Credentials emailed to '.$user->email, type: 'success');
    }

    public function closeCredentialsModal(): void
    {
        $this->showCredentialsModal = false;
        $this->createdCredentials = null;
        $this->dispatch('userSaved');
    }

    public function render(): View
    {
        return view('livewire.user-management.user-form-modal', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}
