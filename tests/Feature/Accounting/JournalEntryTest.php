<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Livewire\Accounting\JournalEntryForm;
use App\Livewire\Accounting\JournalEntryIndex;
use App\Livewire\Accounting\JournalEntryShow;
use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JournalEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    private Account $cashAccount;

    private Account $revenueAccount;

    private FiscalYear $fiscalYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);

        $this->cashAccount = Account::factory()->asset()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1100',
            'name' => 'Cash',
        ]);

        $this->revenueAccount = Account::factory()->revenue()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '4100',
            'name' => 'Sales Revenue',
        ]);

        $this->fiscalYear = FiscalYear::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_renders_journal_entry_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(JournalEntryIndex::class)
            ->assertStatus(200)
            ->assertSee('Journal Entries');
    }

    public function test_status_filter_works(): void
    {
        $this->actingAs($this->admin);

        $draftEntry = JournalEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'fiscal_year_id' => $this->fiscalYear->id,
            'entry_number' => 'JE-000001',
            'status' => 'draft',
            'description' => 'Draft Entry',
        ]);

        $postedEntry = JournalEntry::factory()->posted()->create([
            'tenant_id' => $this->tenant->id,
            'fiscal_year_id' => $this->fiscalYear->id,
            'entry_number' => 'JE-000002',
            'status' => 'posted',
            'description' => 'Posted Entry',
        ]);

        Livewire::test(JournalEntryIndex::class)
            ->set('statusFilter', 'draft')
            ->assertSee('JE-000001')
            ->assertDontSee('JE-000002');
    }

    public function test_can_create_draft_journal_entry(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(JournalEntryForm::class)
            ->set('fiscal_year_id', $this->fiscalYear->id)
            ->set('date', '2026-03-01')
            ->set('description', 'Test journal entry for cash sale')
            ->set('lines', [
                [
                    'account_id' => $this->cashAccount->id,
                    'description' => 'Cash received',
                    'debit' => '500.00',
                    'credit' => '',
                ],
                [
                    'account_id' => $this->revenueAccount->id,
                    'description' => 'Sales revenue',
                    'debit' => '',
                    'credit' => '500.00',
                ],
            ])
            ->call('saveDraft')
            ->assertRedirect();

        $this->assertDatabaseHas('journal_entries', [
            'tenant_id' => $this->tenant->id,
            'fiscal_year_id' => $this->fiscalYear->id,
            'description' => 'Test journal entry for cash sale',
            'status' => 'draft',
        ]);
    }

    public function test_can_view_journal_entry(): void
    {
        $this->actingAs($this->admin);

        $entry = JournalEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'fiscal_year_id' => $this->fiscalYear->id,
            'entry_number' => 'JE-000010',
            'description' => 'Viewable Entry',
            'status' => 'draft',
        ]);

        JournalLine::create([
            'tenant_id' => $this->tenant->id,
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccount->id,
            'debit' => 100,
            'credit' => 0,
        ]);

        JournalLine::create([
            'tenant_id' => $this->tenant->id,
            'journal_entry_id' => $entry->id,
            'account_id' => $this->revenueAccount->id,
            'debit' => 0,
            'credit' => 100,
        ]);

        Livewire::test(JournalEntryShow::class, ['entry' => $entry])
            ->assertStatus(200)
            ->assertSee('JE-000010')
            ->assertSee('Viewable Entry');
    }

    public function test_can_post_draft_entry(): void
    {
        $this->actingAs($this->admin);

        $entry = JournalEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'fiscal_year_id' => $this->fiscalYear->id,
            'entry_number' => 'JE-000020',
            'description' => 'Entry to post',
            'status' => 'draft',
        ]);

        JournalLine::create([
            'tenant_id' => $this->tenant->id,
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccount->id,
            'debit' => 250,
            'credit' => 0,
        ]);

        JournalLine::create([
            'tenant_id' => $this->tenant->id,
            'journal_entry_id' => $entry->id,
            'account_id' => $this->revenueAccount->id,
            'debit' => 0,
            'credit' => 250,
        ]);

        Livewire::test(JournalEntryShow::class, ['entry' => $entry])
            ->call('post')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'status' => 'posted',
        ]);
    }

    public function test_can_void_posted_entry(): void
    {
        $this->actingAs($this->admin);

        $entry = JournalEntry::factory()->posted()->create([
            'tenant_id' => $this->tenant->id,
            'fiscal_year_id' => $this->fiscalYear->id,
            'entry_number' => 'JE-000030',
            'description' => 'Entry to void',
            'posted_by' => $this->admin->id,
        ]);

        JournalLine::create([
            'tenant_id' => $this->tenant->id,
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccount->id,
            'debit' => 300,
            'credit' => 0,
        ]);

        JournalLine::create([
            'tenant_id' => $this->tenant->id,
            'journal_entry_id' => $entry->id,
            'account_id' => $this->revenueAccount->id,
            'debit' => 0,
            'credit' => 300,
        ]);

        Livewire::test(JournalEntryShow::class, ['entry' => $entry])
            ->call('openVoidModal')
            ->set('voidReason', 'Incorrect amount recorded')
            ->call('confirmVoid')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'status' => 'voided',
        ]);
    }
}
