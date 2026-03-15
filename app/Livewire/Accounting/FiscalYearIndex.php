<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Models\FiscalYear;
use App\Services\AccountingService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class FiscalYearIndex extends Component
{
    public bool $showCreateModal = false;

    public string $name = '';

    public string $start_date = '';

    public string $end_date = '';

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'start_date', 'end_date']);
        $this->start_date = now()->startOfYear()->format('Y-m-d');
        $this->end_date = now()->endOfYear()->format('Y-m-d');
        $this->name = 'FY '.now()->year;
        $this->showCreateModal = true;
    }

    public function createFiscalYear(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        FiscalYear::create([
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => 'open',
        ]);

        $this->showCreateModal = false;
        $this->dispatch('toast', message: 'Fiscal year created successfully.', type: 'success');
    }

    public function closeFiscalYear(int $id): void
    {
        try {
            $fy = FiscalYear::findOrFail($id);
            app(AccountingService::class)->closeFiscalYear($fy);
            $this->dispatch('toast', message: "Fiscal year '{$fy->name}' closed successfully.", type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        $fiscalYears = FiscalYear::query()
            ->withCount('journalEntries')
            ->orderByDesc('start_date')
            ->get();

        return view('livewire.accounting.fiscal-year-index', [
            'fiscalYears' => $fiscalYears,
        ]);
    }
}
