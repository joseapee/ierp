<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CrmPipelineStage;
use App\Models\Opportunity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OpportunityService
{
    public function __construct(
        protected SalesOrderService $salesOrderService,
    ) {}

    /**
     * Paginated opportunity listing with filters.
     *
     * @param  array{search?: string, customer_id?: int|string|null, pipeline_stage_id?: int|string|null, assigned_to?: int|string|null}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Opportunity::query()
            ->with(['customer', 'pipelineStage', 'assignedUser'])
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))
            ->when($filters['pipeline_stage_id'] ?? null, fn ($q, $v) => $q->where('pipeline_stage_id', $v))
            ->when($filters['assigned_to'] ?? null, fn ($q, $v) => $q->where('assigned_to', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Opportunity
    {
        $stage = CrmPipelineStage::findOrFail($data['pipeline_stage_id']);

        if (! isset($data['probability'])) {
            $data['probability'] = $stage->win_probability;
        }

        return Opportunity::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Opportunity $opportunity, array $data): Opportunity
    {
        $opportunity->update($data);

        return $opportunity->fresh();
    }

    /**
     * Move opportunity to a different pipeline stage.
     */
    public function moveToStage(Opportunity $opportunity, int $stageId): Opportunity
    {
        $stage = CrmPipelineStage::findOrFail($stageId);

        $opportunity->update([
            'pipeline_stage_id' => $stageId,
            'probability' => $stage->win_probability,
            'closed_at' => ($stage->is_won || $stage->is_lost) ? now() : null,
        ]);

        return $opportunity->fresh(['pipelineStage']);
    }

    /**
     * Mark opportunity as won and optionally create a Sales Order.
     *
     * @param  array<string, mixed>|null  $salesOrderData
     */
    public function markWon(Opportunity $opportunity, ?array $salesOrderData = null): Opportunity
    {
        $wonStage = CrmPipelineStage::where('tenant_id', $opportunity->tenant_id)
            ->where('is_won', true)
            ->first();

        if (! $wonStage) {
            throw new RuntimeException('No "Won" pipeline stage configured.');
        }

        return DB::transaction(function () use ($opportunity, $wonStage, $salesOrderData): Opportunity {
            $opportunity->update([
                'pipeline_stage_id' => $wonStage->id,
                'probability' => 100,
                'closed_at' => now(),
            ]);

            if ($salesOrderData && ! empty($salesOrderData['items'])) {
                $so = $this->salesOrderService->create(array_merge($salesOrderData, [
                    'customer_id' => $opportunity->customer_id,
                ]));
                $opportunity->update(['sales_order_id' => $so->id]);
            }

            return $opportunity->fresh();
        });
    }

    /**
     * Mark opportunity as lost.
     */
    public function markLost(Opportunity $opportunity, ?string $reason = null): Opportunity
    {
        $lostStage = CrmPipelineStage::where('tenant_id', $opportunity->tenant_id)
            ->where('is_lost', true)
            ->first();

        if (! $lostStage) {
            throw new RuntimeException('No "Lost" pipeline stage configured.');
        }

        $opportunity->update([
            'pipeline_stage_id' => $lostStage->id,
            'probability' => 0,
            'closed_at' => now(),
            'lost_reason' => $reason,
        ]);

        return $opportunity->fresh();
    }

    /**
     * Get board data for Kanban view.
     *
     * @param  array{assigned_to?: int|string|null}  $filters
     * @return array<int, array{stage: CrmPipelineStage, opportunities: \Illuminate\Support\Collection<int, Opportunity>}>
     */
    public function getBoardData(array $filters = []): array
    {
        $stages = CrmPipelineStage::active()->ordered()->get();

        $opportunities = Opportunity::query()
            ->with(['customer', 'assignedUser'])
            ->when($filters['assigned_to'] ?? null, fn ($q, $v) => $q->where('assigned_to', $v))
            ->get();

        $boardData = [];
        foreach ($stages as $stage) {
            $boardData[] = [
                'stage' => $stage,
                'opportunities' => $opportunities->where('pipeline_stage_id', $stage->id)->values(),
            ];
        }

        return $boardData;
    }
}
