<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FiscalYear;
use App\Models\JournalEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JournalService
{
    /**
     * Paginated list of journal entries.
     *
     * @param  array{status?: string, search?: string, date_from?: string, date_to?: string, fiscal_year_id?: int}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return JournalEntry::query()
            ->with(['lines.account', 'fiscalYear'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['fiscal_year_id'] ?? null, fn ($q, $v) => $q->where('fiscal_year_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('date', '<=', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(function ($q) use ($v): void {
                $q->where('entry_number', 'like', "%{$v}%")
                    ->orWhere('description', 'like', "%{$v}%")
                    ->orWhere('reference', 'like', "%{$v}%");
            }))
            ->latest('date')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Create a new journal entry with lines (as draft).
     *
     * @param  array{fiscal_year_id: int, date: string, description: string, reference?: string, notes?: string, lines: array<int, array{account_id: int, description?: string, debit: float|string, credit: float|string}>}  $data
     */
    public function create(array $data): JournalEntry
    {
        $this->validateLines($data['lines']);

        return DB::transaction(function () use ($data): JournalEntry {
            $fiscalYear = FiscalYear::findOrFail($data['fiscal_year_id']);
            $this->validateFiscalYearOpen($fiscalYear);

            $entry = JournalEntry::create([
                'fiscal_year_id' => $fiscalYear->id,
                'entry_number' => $this->generateEntryNumber(),
                'date' => $data['date'],
                'description' => $data['description'],
                'reference' => $data['reference'] ?? null,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            return $entry->load('lines.account');
        });
    }

    /**
     * Post a draft journal entry.
     */
    public function post(JournalEntry $entry): JournalEntry
    {
        if ($entry->status !== 'draft') {
            throw new RuntimeException('Only draft entries can be posted.');
        }

        $entry->loadMissing('lines');

        if ($entry->lines->isEmpty() || $entry->lines->count() < 2) {
            throw new RuntimeException('A journal entry must have at least 2 lines.');
        }

        if (! $entry->is_balanced) {
            throw new RuntimeException('Journal entry is not balanced. Total debits must equal total credits.');
        }

        $this->validateFiscalYearOpen($entry->fiscalYear);

        $entry->update([
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        return $entry->fresh(['lines.account', 'fiscalYear']);
    }

    /**
     * Void a posted journal entry by creating a reversing entry.
     */
    public function void(JournalEntry $entry, string $reason): JournalEntry
    {
        if ($entry->status !== 'posted') {
            throw new RuntimeException('Only posted entries can be voided.');
        }

        return DB::transaction(function () use ($entry, $reason): JournalEntry {
            $entry->loadMissing('lines');

            // Create reversing entry.
            $reversal = JournalEntry::create([
                'fiscal_year_id' => $entry->fiscal_year_id,
                'entry_number' => $this->generateEntryNumber(),
                'date' => now()->toDateString(),
                'description' => "Reversal of {$entry->entry_number}: {$reason}",
                'reference' => $entry->entry_number,
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
                'notes' => "Auto-generated reversal. Original entry: {$entry->entry_number}",
            ]);

            foreach ($entry->lines as $line) {
                $reversal->lines()->create([
                    'account_id' => $line->account_id,
                    'description' => $line->description,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                ]);
            }

            // Mark original as voided.
            $entry->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'notes' => trim(($entry->notes ? $entry->notes."\n" : '')."Voided: {$reason}. Reversal: {$reversal->entry_number}"),
            ]);

            return $entry->fresh(['lines.account']);
        });
    }

    /**
     * Create and post a journal entry linked to a source model (e.g. production order, stock adjustment).
     *
     * @param  array<int, array{account_id: int, description?: string, debit: float|string, credit: float|string}>  $lines
     */
    public function createFromSource(Model $source, string $description, array $lines, ?string $reference = null): JournalEntry
    {
        $this->validateLines($lines);

        $fiscalYear = FiscalYear::query()->open()->latest('start_date')->firstOrFail();

        return DB::transaction(function () use ($source, $description, $lines, $reference, $fiscalYear): JournalEntry {
            $entry = JournalEntry::create([
                'fiscal_year_id' => $fiscalYear->id,
                'entry_number' => $this->generateEntryNumber(),
                'date' => now()->toDateString(),
                'description' => $description,
                'reference' => $reference,
                'source_type' => $source->getMorphClass(),
                'source_id' => $source->getKey(),
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            return $entry->load('lines.account');
        });
    }

    /**
     * Generate a unique entry number for the current tenant.
     */
    protected function generateEntryNumber(): string
    {
        $last = JournalEntry::query()
            ->orderByDesc('id')
            ->value('entry_number');

        $nextNum = 1;

        if ($last && preg_match('/JE-(\d+)/', $last, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        }

        return 'JE-'.str_pad((string) $nextNum, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Validate that journal entry lines are balanced.
     *
     * @param  array<int, array{account_id: int, debit: float|string, credit: float|string}>  $lines
     */
    protected function validateLines(array $lines): void
    {
        if (count($lines) < 2) {
            throw new RuntimeException('A journal entry must have at least 2 lines.');
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (bccomp(number_format($totalDebit, 4, '.', ''), number_format($totalCredit, 4, '.', ''), 4) !== 0) {
            throw new RuntimeException('Journal entry is not balanced. Total debits ('.number_format($totalDebit, 4).') must equal total credits ('.number_format($totalCredit, 4).').');
        }
    }

    protected function validateFiscalYearOpen(FiscalYear $fiscalYear): void
    {
        if ($fiscalYear->status !== 'open') {
            throw new RuntimeException("Cannot post to fiscal year '{$fiscalYear->name}' — it is {$fiscalYear->status}.");
        }
    }
}
