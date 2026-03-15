<?php

declare(strict_types=1);

namespace App\Livewire\Accounting;

use App\Models\FiscalYear;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TrialBalance extends Component
{
    public ?int $fiscalYearId = null;

    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');

        $openFy = FiscalYear::query()->open()->latest('start_date')->first();
        if ($openFy) {
            $this->fiscalYearId = $openFy->id;
        }
    }

    public function render(): View
    {
        $asOf = $this->asOfDate ? Carbon::parse($this->asOfDate) : null;
        $report = app(AccountingService::class)->getTrialBalance($this->fiscalYearId, $asOf);

        return view('livewire.accounting.trial-balance', [
            'report' => $report,
            'fiscalYears' => FiscalYear::query()->orderByDesc('start_date')->get(),
        ]);
    }
}
