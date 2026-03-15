<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\Tenant;
use App\Models\User;
use App\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class JournalServiceTest extends TestCase
{
    use RefreshDatabase;

    private JournalService $service;

    private Tenant $tenant;

    private FiscalYear $fiscalYear;

    private Account $cashAccount;

    private Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new JournalService;
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->actingAs(User::factory()->create(['tenant_id' => $this->tenant->id]));

        $this->fiscalYear = FiscalYear::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->cashAccount = Account::factory()->asset()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1000',
            'name' => 'Cash',
        ]);
        $this->revenueAccount = Account::factory()->revenue()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '4000',
            'name' => 'Sales Revenue',
        ]);
    }

    public function test_create_journal_entry(): void
    {
        $entry = $this->service->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Test journal entry',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 1000, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 1000],
            ],
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'fiscal_year_id' => $this->fiscalYear->id,
            'description' => 'Test journal entry',
            'status' => 'draft',
        ]);

        $this->assertCount(2, $entry->lines);
    }

    public function test_create_rejects_unbalanced_entry(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Unbalanced entry',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 1000, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 500],
            ],
        ]);
    }

    public function test_create_rejects_less_than_two_lines(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Single line entry',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 1000, 'credit' => 0],
            ],
        ]);
    }

    public function test_post_journal_entry(): void
    {
        $entry = $this->service->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Entry to post',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 500, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 500],
            ],
        ]);

        $posted = $this->service->post($entry);

        $this->assertEquals('posted', $posted->status);
        $this->assertNotNull($posted->posted_at);
    }

    public function test_post_rejects_already_posted_entry(): void
    {
        $entry = $this->service->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Double post test',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 500, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 500],
            ],
        ]);

        $this->service->post($entry);

        $this->expectException(RuntimeException::class);
        $this->service->post($entry->fresh());
    }

    public function test_void_creates_reversing_entry(): void
    {
        $entry = $this->service->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Entry to void',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 750, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 750],
            ],
        ]);

        $posted = $this->service->post($entry);
        $voided = $this->service->void($posted->fresh(), 'Testing void');

        $this->assertEquals('voided', $voided->status);
        $this->assertNotNull($voided->voided_at);

        // A reversing entry should exist.
        $reversal = JournalEntry::where('reference', $voided->entry_number)
            ->where('status', 'posted')
            ->first();

        $this->assertNotNull($reversal);
        $this->assertStringContainsString('Reversal of', $reversal->description);
    }

    public function test_void_rejects_draft_entry(): void
    {
        $entry = $this->service->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Draft entry',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 200, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 200],
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->service->void($entry, 'Should fail');
    }

    public function test_create_from_source(): void
    {
        $entry = $this->service->createFromSource(
            $this->fiscalYear,
            'Auto-generated from fiscal year',
            [
                ['account_id' => $this->cashAccount->id, 'debit' => 300, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 300],
            ],
            'REF-001'
        );

        $this->assertEquals('posted', $entry->status);
        $this->assertEquals($this->fiscalYear->getMorphClass(), $entry->source_type);
        $this->assertEquals($this->fiscalYear->id, $entry->source_id);
        $this->assertCount(2, $entry->lines);
    }

    public function test_paginate_returns_results(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->service->create([
                'fiscal_year_id' => $this->fiscalYear->id,
                'date' => now()->toDateString(),
                'description' => "Paginate test entry {$i}",
                'lines' => [
                    ['account_id' => $this->cashAccount->id, 'debit' => 100, 'credit' => 0],
                    ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 100],
                ],
            ]);
        }

        $result = $this->service->paginate();

        $this->assertEquals(3, $result->total());
    }
}
