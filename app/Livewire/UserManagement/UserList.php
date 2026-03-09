<?php

declare(strict_types=1);

namespace App\Livewire\UserManagement;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class UserList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $statusFilter = '';

    protected $listeners = [
        'userSaved' => '$refresh',
        'userDeleted' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', $user);

        app(UserService::class)->delete($user);

        $this->dispatch('toast', message: 'User deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        $users = app(UserService::class)->paginate([
            'search' => $this->search,
            'role' => $this->roleFilter,
            'status' => $this->statusFilter ?: null,
        ]);

        return view('livewire.user-management.user-list', [
            'users' => $users,
        ]);
    }
}
