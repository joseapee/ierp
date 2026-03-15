<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Livewire\Accounting\FiscalYearIndex;
use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FiscalYearTest extends TestCase
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

    public function test_renders_fiscal_year_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(FiscalYearIndex::class)
            ->assertStatus(200)
            ->assertSee('Fiscal Year');
    }

    public function test_can_create_fiscal_year(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(FiscalYearIndex::class)
            ->call('openCreateModal')
            ->set('name', 'FY 2025')
            ->set('start_date', '2025-01-01')
            ->set('end_date', '2025-12-31')
            ->call('createFiscalYear')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('fiscal_years', [
            'tenant_id' => $this->tenant->id,
            'name' => 'FY 2025',
            'status' => 'open',
        ]);
    }

    public function test_can_close_fiscal_year(): void
    {
        $this->actingAs($this->admin);

        $fiscalYear = FiscalYear::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'FY 2024',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'open',
        ]);

        // The AccountingService::closeFiscalYear() requires a retained earnings
        // account with code '3100' and type 'equity' to exist.
        Account::factory()->equity()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '3100',
            'name' => 'Retained Earnings',
            'is_system' => true,
        ]);

        Livewire::test(FiscalYearIndex::class)
            ->call('closeFiscalYear', $fiscalYear->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('fiscal_years', [
            'id' => $fiscalYear->id,
            'status' => 'closed',
        ]);
    }
}
