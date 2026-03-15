<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProfitAndLoss extends Component
{
    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render(): View
    {
        $from = Carbon::parse($this->dateFrom ?: now()->startOfYear());
        $to = Carbon::parse($this->dateTo ?: now());

        $report = app(AccountingService::class)->getProfitAndLoss($from, $to);

        return view('livewire.accounting.profit-and-loss', [
            'report' => $report,
        ]);
    }
}
