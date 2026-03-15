<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class JournalEntryShow extends Component
{
    public JournalEntry $entry;

    public bool $showVoidModal = false;

    public string $voidReason = '';

    public function mount(JournalEntry $entry): void
    {
        $this->entry = $entry->load(['lines.account', 'fiscalYear', 'postedByUser', 'voidedByUser']);
    }

    public function post(): void
    {
        try {
            $service = app(JournalService::class);
            $this->entry = $service->post($this->entry);
            $this->dispatch('toast', message: 'Journal entry posted successfully.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openVoidModal(): void
    {
        $this->voidReason = '';
        $this->showVoidModal = true;
    }

    public function confirmVoid(): void
    {
        $this->validate(['voidReason' => 'required|string|max:500']);

        try {
            $service = app(JournalService::class);
            $this->entry = $service->void($this->entry, $this->voidReason);
            $this->showVoidModal = false;
            $this->dispatch('toast', message: 'Journal entry voided. Reversing entry created.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.accounting.journal-entry-show');
    }
}
