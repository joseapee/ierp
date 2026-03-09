<div>
    @section('title', 'Production Board')

    <x-page-header title="Production Board" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Production Board'],
    ]" />

    {{-- Order Selector --}}
    <div class="card custom-card mb-3">
        <div class="card-body py-2">
            <div class="d-flex align-items-center gap-3">
                <label class="form-label mb-0 fw-medium">Production Order:</label>
                <select wire:model.live="orderId" class="form-select form-select-sm" style="width:350px">
                    <option value="">Select an order...</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}">
                            {{ $order->order_number }} — {{ $order->product?->name }}
                            ({{ str_replace('_', ' ', ucfirst($order->status)) }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($orderId)
        @if(empty($boardData))
            <div class="alert alert-info">
                No tasks found for this order. Add tasks from the
                <a href="{{ route('manufacturing.orders.show', $orderId) }}" wire:navigate>order detail page</a>.
            </div>
        @else
            <div class="row g-3">
                @foreach($boardData as $column)
                <div class="col">
                    <div class="card custom-card h-100">
                        <div class="card-header bg-light">
                            <div class="card-title mb-0">
                                {{ $column['stage_name'] }}
                                <span class="badge bg-primary-transparent ms-1">{{ $column['tasks']->count() }}</span>
                            </div>
                        </div>
                        <div class="card-body p-2" style="min-height: 200px;">
                            @foreach($column['tasks'] as $task)
                            <div class="card mb-2 border shadow-sm" wire:key="board-task-{{ $task->id }}">
                                <div class="card-body p-2">
                                    <div class="fw-medium small">{{ $task->name }}</div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="badge bg-{{ match($task->status) { 'completed' => 'success', 'in_progress' => 'primary', default => 'secondary' } }}-transparent" style="font-size: 0.7rem">
                                            {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                        </span>
                                        @if($task->assignedUser)
                                            <small class="text-muted">{{ $task->assignedUser->name }}</small>
                                        @endif
                                    </div>
                                    <div class="mt-2 d-flex gap-1 flex-wrap">
                                        @if($task->status === 'pending')
                                            <button class="btn btn-xs btn-outline-primary" wire:click="startTask({{ $task->id }})" style="font-size:0.7rem;padding:2px 6px;">Start</button>
                                        @elseif($task->status === 'in_progress')
                                            <button class="btn btn-xs btn-outline-success" wire:click="completeTask({{ $task->id }})" style="font-size:0.7rem;padding:2px 6px;">Complete</button>
                                        @endif
                                        {{-- Move to next stage buttons --}}
                                        @foreach($stages as $stage)
                                            @if($stage->id !== $task->current_stage_id)
                                                <button class="btn btn-xs btn-outline-secondary" wire:click="moveTask({{ $task->id }}, {{ $stage->id }})" style="font-size:0.65rem;padding:1px 4px;" title="Move to {{ $stage->name }}">
                                                    &rarr; {{ Str::limit($stage->name, 10) }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="alert alert-secondary">
            Select a production order to view its board.
        </div>
    @endif
</div>
