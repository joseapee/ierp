<?php

declare(strict_types=1);

namespace App\Livewire\UserManagement;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserViewModal extends Component
{
    public bool $showModal = false;

    public ?User $user = null;

    protected $listeners = [
        'openUserViewModal' => 'open',
    ];

    public function open(int $userId): void
    {
        $this->user = User::with(['roles', 'tenant'])->findOrFail($userId);
        $this->showModal = true;
    }

    public function render(): View
    {
        return view('livewire.user-management.user-view-modal');
    }
}
