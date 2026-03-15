<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Models\FiscalYear;
use App\Services\JournalService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class JournalEntryIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $fiscalYearFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedFiscalYearFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $entries = app(JournalService::class)->paginate([
            'search' => $this->search,
            'status' => $this->statusFilter ?: null,
            'date_from' => $this->dateFrom ?: null,
            'date_to' => $this->dateTo ?: null,
            'fiscal_year_id' => $this->fiscalYearFilter ?: null,
        ]);

        return view('livewire.accounting.journal-entry-index', [
            'entries' => $entries,
            'fiscalYears' => FiscalYear::query()->orderByDesc('start_date')->get(),
        ]);
    }
}
