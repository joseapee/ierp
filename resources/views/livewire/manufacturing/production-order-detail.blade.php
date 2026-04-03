<div>
    @section('title', 'Order ' . $order->order_number)

    <x-page-header :title="'Order ' . $order->order_number" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Production Orders', 'route' => 'manufacturing.orders.index'],
        ['label' => $order->order_number],
    ]">
        <x-slot:actions>
            @if($order->status === 'draft')
                @can('production.manage')
                    <button class="btn btn-info btn-wave" wire:click="confirmOrder" wire:confirm="Confirm this order?">
                        <i class="ri-check-line me-1"></i> Confirm
                    </button>
                @endcan
                @can('production.manage')
                    <button class="btn btn-danger btn-wave" wire:click="cancelOrder" wire:confirm="Cancel this order?">
                        <i class="ri-close-line me-1"></i> Cancel
                    </button>
                @endcan
            @elseif($order->status === 'confirmed')
                @can('production.manage')
                    <button class="btn btn-primary btn-wave" wire:click="startProduction">
                        <i class="ri-play-line me-1"></i> Start Production
                    </button>
                @endcan
                @can('production.manage')
                    <button class="btn btn-danger btn-wave" wire:click="cancelOrder" wire:confirm="Cancel this order?">
                        <i class="ri-close-line me-1"></i> Cancel
                    </button>
                @endcan
            @elseif($order->status === 'in_progress')
                @can('production.approve')
                    <button class="btn btn-success btn-wave" wire:click="openCompleteModal">
                        <i class="ri-check-double-line me-1"></i> Complete
                    </button>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        {{-- Order Info --}}
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Order Information</div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Order #</span>
                            <code>{{ $order->order_number }}</code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Product</span>
                            <span>{{ $order->product?->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">BOM</span>
                            <span>{{ $order->bom?->name }} (v{{ $order->bom?->version }})</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Warehouse</span>
                            <span>{{ $order->warehouse?->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-{{ match($order->status) { 'completed' => 'success', 'in_progress' => 'primary', 'confirmed' => 'info', 'draft' => 'warning', default => 'danger' } }}-transparent">
                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Priority</span>
                            <span class="badge bg-{{ match($order->priority) { 'urgent' => 'danger', 'high' => 'warning', 'normal' => 'info', default => 'secondary' } }}-transparent">
                                {{ ucfirst($order->priority) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Quantities & Cost</div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Planned Qty</span>
                            <span>{{ number_format((float)$order->planned_quantity, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Completed Qty</span>
                            <span>{{ number_format((float)$order->completed_quantity, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Rejected Qty</span>
                            <span>{{ number_format((float)$order->rejected_quantity, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Unit Cost</span>
                            <span>{{ format_currency((float)$order->unit_cost, 4) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Total Cost</span>
                            <span class="fw-bold">{{ format_currency((float)$order->total_cost) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if($order->notes)
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Notes</div></div>
                <div class="card-body"><p class="mb-0">{{ $order->notes }}</p></div>
            </div>
            @endif
        </div>

        {{-- Right column --}}
        <div class="col-xl-8">
            {{-- BOM Materials --}}
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">BOM Materials</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th class="text-end">Qty Required</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Total</th>
                                    @if($order->status === 'in_progress')
                                    <th class="text-end">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->bom?->items ?? [] as $item)
                                @php
                                    $reqQty = (float)$item->quantity * (float)$order->planned_quantity;
                                @endphp
                                <tr>
                                    <td>{{ $item->product?->name }}</td>
                                    <td class="text-end">{{ number_format($reqQty, 2) }}</td>
                                    <td class="text-end">{{ format_currency((float)$item->unit_cost, 4) }}</td>
                                    <td class="text-end">{{ format_currency($reqQty * (float)$item->unit_cost) }}</td>
                                    @if($order->status === 'in_progress')
                                    <td class="text-end">
                                        @can('production.manage')
                                        <button class="btn btn-sm btn-outline-warning btn-wave"
                                                wire:click="openConsumeModal({{ $item->product_id }}, {{ $reqQty }}, {{ $item->unit_cost }})">
                                            <i class="ri-subtract-line"></i> Consume
                                        </button>
                                        @endcan
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Material Consumptions --}}
            @if($order->materialConsumptions->count() > 0)
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Material Consumptions</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th class="text-end">Planned</th>
                                    <th class="text-end">Actual</th>
                                    <th class="text-end">Wastage</th>
                                    <th class="text-end">Total Cost</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->materialConsumptions as $cons)
                                <tr>
                                    <td>{{ $cons->product?->name }}</td>
                                    <td class="text-end">{{ number_format((float)$cons->planned_quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format((float)$cons->actual_quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format((float)$cons->wastage_quantity, 2) }}</td>
                                    <td class="text-end">{{ format_currency((float)$cons->total_cost) }}</td>
                                    <td>{{ format_datetime($cons->consumed_at) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end fw-medium">Total Consumed Cost:</td>
                                    <td class="text-end fw-bold">{{ format_currency((float)$order->materialConsumptions->sum('total_cost')) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Production Tasks --}}
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Production Tasks</div>
                    @if(in_array($order->status, ['in_progress', 'confirmed']))
                        @can('production.manage')
                        <button class="btn btn-sm btn-primary btn-wave" wire:click="openTaskModal">
                            <i class="ri-add-line me-1"></i> Add Task
                        </button>
                        @endcan
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Stage</th>
                                    <th>Status</th>
                                    <th>Assigned</th>
                                    <th>Duration</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->tasks as $task)
                                <tr wire:key="task-{{ $task->id }}">
                                    <td><span class="fw-medium">{{ $task->name }}</span></td>
                                    <td>{{ $task->currentStage?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ match($task->status) { 'completed' => 'success', 'in_progress' => 'primary', default => 'secondary' } }}-transparent">
                                            {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $task->assignedUser?->name ?? '—' }}</td>
                                    <td>
                                        @if($task->actual_duration_minutes)
                                            {{ $task->actual_duration_minutes }} min
                                        @elseif($task->estimated_duration_minutes)
                                            ~{{ $task->estimated_duration_minutes }} min
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('production.manage')
                                        @if($task->status === 'pending')
                                            <button class="btn btn-sm btn-outline-primary btn-wave" wire:click="startTask({{ $task->id }})">
                                                <i class="ri-play-line"></i>
                                            </button>
                                        @elseif($task->status === 'in_progress')
                                            <button class="btn btn-sm btn-outline-success btn-wave" wire:click="completeTask({{ $task->id }})">
                                                <i class="ri-check-line"></i>
                                            </button>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No tasks yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Consume Material Modal --}}
    @if($showConsumeModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Consume Material</h6>
                    <button type="button" class="btn-close" wire:click="$set('showConsumeModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Warehouse</label>
                        <select wire:model.live="consume_warehouse_id" class="form-select">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Batch (optional)</label>
                        <select wire:model="consume_batch_id" class="form-select">
                            <option value="">Auto-select</option>
                            @foreach($availableBatches as $batch)
                                <option value="{{ $batch->id }}">
                                    {{ $batch->batch_number ?? 'Batch #'.$batch->id }} — Remaining: {{ number_format((float)$batch->remaining_quantity, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Planned Qty</label>
                            <input type="number" class="form-control" value="{{ $consume_planned_qty }}" disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Actual Qty <span class="text-danger">*</span></label>
                            <input type="number" wire:model="consume_actual_qty" class="form-control" step="0.01" min="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" wire:model="consume_unit_cost" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Wastage Qty</label>
                            <input type="number" wire:model="consume_wastage_qty" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="consume_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showConsumeModal', false)">Cancel</button>
                    <button type="button" class="btn btn-warning btn-wave" wire:click="consumeMaterial" wire:loading.attr="disabled">
                        <span wire:loading wire:target="consumeMaterial" class="spinner-border spinner-border-sm me-1"></span>
                        Consume
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Task Modal --}}
    @if($showTaskModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add Task</h6>
                    <button type="button" class="btn-close" wire:click="$set('showTaskModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="task_name" class="form-control @error('task_name') is-invalid @enderror" placeholder="e.g. Cut leather pieces">
                        @error('task_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stage</label>
                        <select wire:model="task_stage_id" class="form-select">
                            <option value="">No stage</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="task_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showTaskModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="createTask" wire:loading.attr="disabled">
                        <span wire:loading wire:target="createTask" class="spinner-border spinner-border-sm me-1"></span>
                        Create Task
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Complete Production Modal --}}
    @if($showCompleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Complete Production</h6>
                    <button type="button" class="btn-close" wire:click="$set('showCompleteModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        This will create finished goods inventory and close the order.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Completed Quantity <span class="text-danger">*</span></label>
                        <input type="number" wire:model="completed_quantity" class="form-control @error('completed_quantity') is-invalid @enderror" step="0.01" min="0.01">
                        @error('completed_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejected Quantity</label>
                        <input type="number" wire:model="rejected_quantity" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="text-muted small">
                        Total material cost: <strong>{{ format_currency((float)$order->materialConsumptions->sum('total_cost')) }}</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showCompleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success btn-wave" wire:click="completeProduction" wire:loading.attr="disabled">
                        <span wire:loading wire:target="completeProduction" class="spinner-border spinner-border-sm me-1"></span>
                        Complete Production
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
