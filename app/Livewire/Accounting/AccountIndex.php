<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AccountIndex extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingAccountId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'asset';

    public ?string $sub_type = null;

    public ?int $parent_id = null;

    public string $normal_balance = 'debit';

    public string $description = '';

    public bool $is_active = true;

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['editingAccountId', 'code', 'name', 'type', 'sub_type', 'parent_id', 'normal_balance', 'description', 'is_active']);
        $this->is_active = true;
        $this->type = 'asset';
        $this->normal_balance = 'debit';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $account = Account::findOrFail($id);
        $this->resetValidation();
        $this->editingAccountId = $account->id;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->sub_type = $account->sub_type;
        $this->parent_id = $account->parent_id;
        $this->normal_balance = $account->normal_balance;
        $this->description = $account->description ?? '';
        $this->is_active = $account->is_active;
        $this->showModal = true;
    }

    public function updatedType(): void
    {
        $this->normal_balance = in_array($this->type, ['asset', 'expense']) ? 'debit' : 'credit';
    }

    public function save(): void
    {
        $rules = [
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
        ];

        $this->validate($rules);

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'sub_type' => $this->sub_type ?: null,
            'parent_id' => $this->parent_id ?: null,
            'normal_balance' => $this->normal_balance,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingAccountId) {
            $account = Account::findOrFail($this->editingAccountId);

            if ($account->is_system) {
                $this->dispatch('toast', message: 'System accounts cannot be modified.', type: 'error');

                return;
            }

            $account->update($data);
            $this->dispatch('toast', message: 'Account updated successfully.', type: 'success');
        } else {
            Account::create($data);
            $this->dispatch('toast', message: 'Account created successfully.', type: 'success');
        }

        $this->showModal = false;
    }

    public function deleteAccount(int $id): void
    {
        $account = Account::findOrFail($id);

        if ($account->is_system) {
            $this->dispatch('toast', message: 'System accounts cannot be deleted.', type: 'error');

            return;
        }

        if ($account->journalLines()->exists()) {
            $this->dispatch('toast', message: 'Cannot delete account with journal entries.', type: 'error');

            return;
        }

        $account->delete();
        $this->dispatch('toast', message: 'Account deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        $accounts = Account::query()
            ->with('parent')
            ->when($this->search, fn ($q, $v) => $q->where(function ($q) use ($v): void {
                $q->where('code', 'like', "%{$v}%")
                    ->orWhere('name', 'like', "%{$v}%");
            }))
            ->when($this->typeFilter, fn ($q, $v) => $q->where('type', $v))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('code')
            ->get();

        $parentAccounts = Account::query()
            ->active()
            ->orderBy('code')
            ->get();

        return view('livewire.accounting.account-index', [
            'accounts' => $accounts,
            'parentAccounts' => $parentAccounts,
        ]);
    }
}
