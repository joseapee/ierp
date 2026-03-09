<?php

declare(strict_types=1);

namespace App\Livewire\Manufacturing;

use App\Models\ProductionOrder;
use App\Models\ProductionStage;
use App\Services\ProductionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductionBoard extends Component
{
    #[Url]
    public ?int $orderId = null;

    public function moveTask(int $taskId, int $stageId): void
    {
        $task = \App\Models\ProductionTask::findOrFail($taskId);
        app(ProductionService::class)->moveTask($task, $stageId);
        $this->dispatch('toast', message: 'Task moved.', type: 'success');
    }

    public function startTask(int $taskId): void
    {
        $task = \App\Models\ProductionTask::findOrFail($taskId);
        app(ProductionService::class)->startTask($task);
    }

    public function completeTask(int $taskId): void
    {
        $task = \App\Models\ProductionTask::findOrFail($taskId);
        app(ProductionService::class)->completeTask($task);
    }

    public function render(): View
    {
        $stages = ProductionStage::where('is_active', true)->orderBy('sort_order')->get();

        $orders = ProductionOrder::with('product')
            ->whereIn('status', ['in_progress', 'confirmed'])
            ->latest()
            ->get();

        $boardData = [];
        if ($this->orderId) {
            $boardData = app(ProductionService::class)->getBoardData($this->orderId);
        }

        return view('livewire.manufacturing.production-board', [
            'stages' => $stages,
            'orders' => $orders,
            'boardData' => $boardData,
        ]);
    }
}
