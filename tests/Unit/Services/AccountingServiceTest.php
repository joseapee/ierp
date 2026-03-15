<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\JournalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingServiceTest extends TestCase
{
    use RefreshDatabase;

    private AccountingService $service;

    private JournalService $journalService;

    private Tenant $tenant;

    private FiscalYear $fiscalYear;

    private Account $cashAccount;

    private Account $revenueAccount;

    private Account $retainedEarningsAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = new JournalService;
        $this->service = new AccountingService($this->journalService);
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

        $this->retainedEarningsAccount = Account::factory()->equity()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '3100',
            'name' => 'Retained Earnings',
        ]);

        // Create and post a balanced journal entry: debit Cash 1000, credit Revenue 1000.
        $entry = $this->journalService->create([
            'fiscal_year_id' => $this->fiscalYear->id,
            'date' => now()->toDateString(),
            'description' => 'Initial posted entry',
            'lines' => [
                ['account_id' => $this->cashAccount->id, 'debit' => 1000, 'credit' => 0],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 1000],
            ],
        ]);

        $this->journalService->post($entry);
    }

    public function test_get_account_balance_for_debit_account(): void
    {
        $balance = $this->service->getAccountBalance($this->cashAccount);

        $this->assertEquals(1000.0, $balance);
    }

    public function test_get_account_balance_for_credit_account(): void
    {
        $balance = $this->service->getAccountBalance($this->revenueAccount);

        $this->assertEquals(1000.0, $balance);
    }

    public function test_get_trial_balance(): void
    {
        $trialBalance = $this->service->getTrialBalance($this->fiscalYear->id);

        $this->assertEquals($trialBalance['total_debit'], $trialBalance['total_credit']);
    }

    public function test_get_balance_sheet(): void
    {
        $balanceSheet = $this->service->getBalanceSheet();

        $this->assertEquals(1000.0, $balanceSheet['total_assets']);
        $this->assertEquals(1000.0, $balanceSheet['retained_earnings']);
    }

    public function test_get_profit_and_loss(): void
    {
        $from = Carbon::parse($this->fiscalYear->start_date);
        $to = Carbon::parse($this->fiscalYear->end_date);

        $pnl = $this->service->getProfitAndLoss($from, $to);

        $this->assertEquals(1000.0, $pnl['total_revenue']);
        $this->assertEquals(0.0, $pnl['total_expenses']);
        $this->assertEquals(1000.0, $pnl['net_profit']);
    }

    public function test_close_fiscal_year(): void
    {
        $this->service->closeFiscalYear($this->fiscalYear);

        $this->fiscalYear->refresh();

        $this->assertEquals('closed', $this->fiscalYear->status);
    }
}
