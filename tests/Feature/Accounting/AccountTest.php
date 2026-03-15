<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Livewire\Accounting\AccountIndex;
use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('accounting.accounts.index'))->assertRedirect(route('login'));
    }

    public function test_renders_account_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AccountIndex::class)
            ->assertStatus(200)
            ->assertSee('Accounts');
    }

    public function test_search_filters_accounts(): void
    {
        $this->actingAs($this->admin);

        Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1100',
            'name' => 'Cash on Hand',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);

        Account::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '2100',
            'name' => 'Accounts Payable',
            'type' => 'liability',
            'normal_balance' => 'credit',
        ]);

        Livewire::test(AccountIndex::class)
            ->set('search', 'Cash')
            ->assertSee('Cash on Hand')
            ->assertDontSee('Accounts Payable');
    }

    public function test_type_filter_works(): void
    {
        $this->actingAs($this->admin);

        Account::factory()->asset()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1200',
            'name' => 'Bank Account',
        ]);

        Account::factory()->liability()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '2200',
            'name' => 'Loan Payable',
        ]);

        Livewire::test(AccountIndex::class)
            ->set('typeFilter', 'asset')
            ->assertSee('Bank Account')
            ->assertDontSee('Loan Payable');
    }

    public function test_can_create_account(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AccountIndex::class)
            ->call('openCreateModal')
            ->set('code', '1500')
            ->set('name', 'Office Equipment')
            ->set('type', 'asset')
            ->set('normal_balance', 'debit')
            ->call('save')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('accounts', [
            'tenant_id' => $this->tenant->id,
            'code' => '1500',
            'name' => 'Office Equipment',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);
    }

    public function test_can_edit_account(): void
    {
        $this->actingAs($this->admin);

        $account = Account::factory()->asset()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1300',
            'name' => 'Old Name',
            'is_system' => false,
        ]);

        Livewire::test(AccountIndex::class)
            ->call('openEditModal', $account->id)
            ->set('name', 'Updated Name')
            ->call('save')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_cannot_delete_system_account(): void
    {
        $this->actingAs($this->admin);

        $account = Account::factory()->asset()->system()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1000',
            'name' => 'System Cash',
        ]);

        Livewire::test(AccountIndex::class)
            ->call('deleteAccount', $account->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }

    public function test_can_delete_non_system_account(): void
    {
        $this->actingAs($this->admin);

        $account = Account::factory()->asset()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1400',
            'name' => 'Deletable Account',
            'is_system' => false,
        ]);

        Livewire::test(AccountIndex::class)
            ->call('deleteAccount', $account->id)
            ->assertDispatched('toast');

        $this->assertSoftDeleted('accounts', ['id' => $account->id]);
    }
}
