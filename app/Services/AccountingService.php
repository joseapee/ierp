<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountingService
{
    public function __construct(
        protected JournalService $journalService,
    ) {}

    /**
     * Get the balance for a single account as of a given date.
     * Returns positive for normal balance direction, negative for contra.
     */
    public function getAccountBalance(Account $account, ?Carbon $asOf = null): float
    {
        $query = JournalLine::query()
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($asOf): void {
                $q->where('status', 'posted');
                if ($asOf) {
                    $q->where('date', '<=', $asOf->toDateString());
                }
            });

        $totals = $query->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')->first();

        $debit = (float) $totals->total_debit;
        $credit = (float) $totals->total_credit;

        return $account->normal_balance === 'debit'
            ? $debit - $credit
            : $credit - $debit;
    }

    /**
     * Get the trial balance for a fiscal year as of a given date.
     *
     * @return array{accounts: Collection, total_debit: float, total_credit: float}
     */
    public function getTrialBalance(?int $fiscalYearId = null, ?Carbon $asOf = null): array
    {
        $query = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'posted')
            ->when($fiscalYearId, fn ($q, $v) => $q->where('journal_entries.fiscal_year_id', $v))
            ->when($asOf, fn ($q, $v) => $q->where('journal_entries.date', '<=', $v->toDateString()))
            ->select(
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.normal_balance',
                DB::raw('COALESCE(SUM(journal_lines.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_lines.credit), 0) as total_credit'),
            )
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->orderBy('accounts.code');

        $accounts = $query->get()->map(function ($row) {
            $balance = (float) $row->total_debit - (float) $row->total_credit;
            $row->debit_balance = $balance > 0 ? $balance : 0;
            $row->credit_balance = $balance < 0 ? abs($balance) : 0;

            return $row;
        });

        return [
            'accounts' => $accounts,
            'total_debit' => $accounts->sum('debit_balance'),
            'total_credit' => $accounts->sum('credit_balance'),
        ];
    }

    /**
     * Get the balance sheet as of a given date.
     *
     * @return array{assets: Collection, liabilities: Collection, equity: Collection, total_assets: float, total_liabilities: float, total_equity: float, retained_earnings: float}
     */
    public function getBalanceSheet(?Carbon $asOf = null): array
    {
        $accounts = Account::query()
            ->active()
            ->whereIn('type', ['asset', 'liability', 'equity'])
            ->orderBy('code')
            ->get();

        $grouped = ['asset' => collect(), 'liability' => collect(), 'equity' => collect()];

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account, $asOf);

            if (abs($balance) > 0.0001) {
                $grouped[$account->type]->push((object) [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'sub_type' => $account->sub_type,
                    'balance' => $balance,
                ]);
            }
        }

        // Calculate retained earnings from revenue - expenses.
        $retainedEarnings = $this->calculateRetainedEarnings($asOf);

        $totalAssets = $grouped['asset']->sum('balance');
        $totalLiabilities = $grouped['liability']->sum('balance');
        $totalEquity = $grouped['equity']->sum('balance') + $retainedEarnings;

        return [
            'assets' => $grouped['asset'],
            'liabilities' => $grouped['liability'],
            'equity' => $grouped['equity'],
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'retained_earnings' => $retainedEarnings,
        ];
    }

    /**
     * Get the Profit & Loss statement for a date range.
     *
     * @return array{revenue: Collection, expenses: Collection, total_revenue: float, total_expenses: float, net_profit: float}
     */
    public function getProfitAndLoss(Carbon $from, Carbon $to): array
    {
        $accounts = Account::query()
            ->active()
            ->whereIn('type', ['revenue', 'expense'])
            ->orderBy('code')
            ->get();

        $grouped = ['revenue' => collect(), 'expense' => collect()];

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalanceBetween($account, $from, $to);

            if (abs($balance) > 0.0001) {
                $grouped[$account->type]->push((object) [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'sub_type' => $account->sub_type,
                    'balance' => $balance,
                ]);
            }
        }

        $totalRevenue = $grouped['revenue']->sum('balance');
        $totalExpenses = $grouped['expense']->sum('balance');

        return [
            'revenue' => $grouped['revenue'],
            'expenses' => $grouped['expense'],
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalRevenue - $totalExpenses,
        ];
    }

    /**
     * Close a fiscal year by transferring net income to retained earnings.
     */
    public function closeFiscalYear(FiscalYear $fiscalYear): void
    {
        if ($fiscalYear->status !== 'open') {
            throw new RuntimeException("Fiscal year '{$fiscalYear->name}' is already {$fiscalYear->status}.");
        }

        DB::transaction(function () use ($fiscalYear): void {
            $retainedEarnings = Account::query()
                ->where('code', '3100')
                ->where('type', 'equity')
                ->firstOrFail();

            // Sum all revenue and expense entries for this fiscal year.
            $revenueAccounts = Account::query()->where('type', 'revenue')->pluck('id');
            $expenseAccounts = Account::query()->where('type', 'expense')->pluck('id');

            $revenueTotal = JournalLine::query()
                ->whereIn('account_id', $revenueAccounts)
                ->whereHas('journalEntry', fn ($q) => $q->where('fiscal_year_id', $fiscalYear->id)->where('status', 'posted'))
                ->selectRaw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as net')
                ->value('net');

            $expenseTotal = JournalLine::query()
                ->whereIn('account_id', $expenseAccounts)
                ->whereHas('journalEntry', fn ($q) => $q->where('fiscal_year_id', $fiscalYear->id)->where('status', 'posted'))
                ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as net')
                ->value('net');

            $netIncome = (float) $revenueTotal - (float) $expenseTotal;

            // Create closing journal entry if there's a non-zero net income.
            if (abs($netIncome) > 0.0001) {
                $lines = [];

                // Close revenue accounts (debit revenue, reducing their credit balances).
                foreach ($revenueAccounts as $accountId) {
                    $balance = JournalLine::query()
                        ->where('account_id', $accountId)
                        ->whereHas('journalEntry', fn ($q) => $q->where('fiscal_year_id', $fiscalYear->id)->where('status', 'posted'))
                        ->selectRaw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as net')
                        ->value('net');

                    if (abs((float) $balance) > 0.0001) {
                        $lines[] = [
                            'account_id' => $accountId,
                            'description' => 'Year-end closing',
                            'debit' => max(0, (float) $balance),
                            'credit' => max(0, -(float) $balance),
                        ];
                    }
                }

                // Close expense accounts (credit expenses, reducing their debit balances).
                foreach ($expenseAccounts as $accountId) {
                    $balance = JournalLine::query()
                        ->where('account_id', $accountId)
                        ->whereHas('journalEntry', fn ($q) => $q->where('fiscal_year_id', $fiscalYear->id)->where('status', 'posted'))
                        ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as net')
                        ->value('net');

                    if (abs((float) $balance) > 0.0001) {
                        $lines[] = [
                            'account_id' => $accountId,
                            'description' => 'Year-end closing',
                            'debit' => max(0, -(float) $balance),
                            'credit' => max(0, (float) $balance),
                        ];
                    }
                }

                // Transfer net income to retained earnings.
                $lines[] = [
                    'account_id' => $retainedEarnings->id,
                    'description' => 'Net income transfer for '.$fiscalYear->name,
                    'debit' => $netIncome < 0 ? abs($netIncome) : 0,
                    'credit' => $netIncome > 0 ? $netIncome : 0,
                ];

                $closingEntry = $this->journalService->create([
                    'fiscal_year_id' => $fiscalYear->id,
                    'date' => $fiscalYear->end_date->toDateString(),
                    'description' => "Closing entry for {$fiscalYear->name}",
                    'reference' => 'YEAR-END-CLOSE',
                    'lines' => $lines,
                ]);

                $this->journalService->post($closingEntry);
            }

            $fiscalYear->update([
                'status' => 'closed',
                'closed_by' => auth()->id(),
                'closed_at' => now(),
            ]);
        });
    }

    /**
     * Get balance for an account within a specific date range.
     */
    protected function getAccountBalanceBetween(Account $account, Carbon $from, Carbon $to): float
    {
        $totals = JournalLine::query()
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($from, $to): void {
                $q->where('status', 'posted')
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
            })
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->first();

        $debit = (float) $totals->total_debit;
        $credit = (float) $totals->total_credit;

        return $account->normal_balance === 'debit'
            ? $debit - $credit
            : $credit - $debit;
    }

    /**
     * Calculate retained earnings (cumulative net income from all periods).
     */
    protected function calculateRetainedEarnings(?Carbon $asOf = null): float
    {
        $revenueAccounts = Account::query()->where('type', 'revenue')->pluck('id');
        $expenseAccounts = Account::query()->where('type', 'expense')->pluck('id');

        $revenueTotal = JournalLine::query()
            ->whereIn('account_id', $revenueAccounts)
            ->whereHas('journalEntry', function ($q) use ($asOf): void {
                $q->where('status', 'posted');
                if ($asOf) {
                    $q->where('date', '<=', $asOf->toDateString());
                }
            })
            ->selectRaw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as net')
            ->value('net');

        $expenseTotal = JournalLine::query()
            ->whereIn('account_id', $expenseAccounts)
            ->whereHas('journalEntry', function ($q) use ($asOf): void {
                $q->where('status', 'posted');
                if ($asOf) {
                    $q->where('date', '<=', $asOf->toDateString());
                }
            })
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as net')
            ->value('net');

        return (float) $revenueTotal - (float) $expenseTotal;
    }
}
