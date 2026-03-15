<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AccountForm extends Component
{
    public ?int $accountId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'asset';

    public ?string $sub_type = null;

    public ?int $parent_id = null;

    public string $normal_balance = 'debit';

    public string $description = '';

    public bool $is_active = true;

    public function mount(?Account $account = null): void
    {
        if ($account && $account->exists) {
            $this->accountId = $account->id;
            $this->code = $account->code;
            $this->name = $account->name;
            $this->type = $account->type;
            $this->sub_type = $account->sub_type;
            $this->parent_id = $account->parent_id;
            $this->normal_balance = $account->normal_balance;
            $this->description = $account->description ?? '';
            $this->is_active = $account->is_active;
        }
    }

    public function updatedType(): void
    {
        $this->normal_balance = in_array($this->type, ['asset', 'expense']) ? 'debit' : 'credit';
    }

    public function save(): void
    {
        $this->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
        ]);

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

        if ($this->accountId) {
            $account = Account::findOrFail($this->accountId);

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

        $this->redirect(route('accounting.accounts.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.accounting.account-form', [
            'parentAccounts' => Account::query()->active()->orderBy('code')->get(),
        ]);
    }
}
