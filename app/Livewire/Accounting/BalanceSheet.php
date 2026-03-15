<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BalanceSheet extends Component
{
    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function render(): View
    {
        $asOf = $this->asOfDate ? Carbon::parse($this->asOfDate) : null;
        $report = app(AccountingService::class)->getBalanceSheet($asOf);

        return view('livewire.accounting.balance-sheet', [
            'report' => $report,
        ]);
    }
}
