<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Models\Account;
use App\Models\FiscalYear;
use App\Services\JournalService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class JournalEntryForm extends Component
{
    public ?int $fiscal_year_id = null;

    public string $date = '';

    public string $description = '';

    public string $reference = '';

    public string $notes = '';

    public array $lines = [];

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');

        $openFy = FiscalYear::query()->open()->latest('start_date')->first();
        if ($openFy) {
            $this->fiscal_year_id = $openFy->id;
        }

        $this->lines = [
            ['account_id' => null, 'description' => '', 'debit' => '', 'credit' => ''],
            ['account_id' => null, 'description' => '', 'debit' => '', 'credit' => ''],
        ];
    }

    public function addLine(): void
    {
        $this->lines[] = ['account_id' => null, 'description' => '', 'debit' => '', 'credit' => ''];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 2) {
            $this->dispatch('toast', message: 'A journal entry must have at least 2 lines.', type: 'warning');

            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function getTotalDebitProperty(): float
    {
        return collect($this->lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0));
    }

    public function getTotalCreditProperty(): float
    {
        return collect($this->lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0));
    }

    public function getIsBalancedProperty(): bool
    {
        return bccomp(
            number_format($this->totalDebit, 4, '.', ''),
            number_format($this->totalCredit, 4, '.', ''),
            4
        ) === 0;
    }

    public function getDifferenceProperty(): float
    {
        return abs($this->totalDebit - $this->totalCredit);
    }

    public function saveDraft(): void
    {
        $this->saveEntry(false);
    }

    public function saveAndPost(): void
    {
        $this->saveEntry(true);
    }

    private function saveEntry(bool $post): void
    {
        $this->validate([
            'fiscal_year_id' => 'required|integer|exists:fiscal_years,id',
            'date' => 'required|date',
            'description' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|integer|exists:accounts,id',
        ]);

        try {
            $service = app(JournalService::class);

            $entry = $service->create([
                'fiscal_year_id' => $this->fiscal_year_id,
                'date' => $this->date,
                'description' => $this->description,
                'reference' => $this->reference ?: null,
                'notes' => $this->notes ?: null,
                'lines' => array_map(fn ($l) => [
                    'account_id' => $l['account_id'],
                    'description' => $l['description'] ?: null,
                    'debit' => (float) ($l['debit'] ?? 0),
                    'credit' => (float) ($l['credit'] ?? 0),
                ], $this->lines),
            ]);

            if ($post) {
                $service->post($entry);
                $this->dispatch('toast', message: 'Journal entry created and posted.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'Journal entry saved as draft.', type: 'success');
            }

            $this->redirect(route('accounting.journal-entries.show', $entry), navigate: true);
        } catch (RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.accounting.journal-entry-form', [
            'accounts' => Account::query()->active()->orderBy('code')->get(),
            'fiscalYears' => FiscalYear::query()->open()->orderByDesc('start_date')->get(),
        ]);
    }
}
