<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CrmActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CrmActivityService
{
    /**
     * Paginated activity listing with filters.
     *
     * @param  array{search?: string, type?: string, status?: string, assigned_to?: int|string|null, related_to_type?: string, related_to_id?: int|null}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CrmActivity::query()
            ->with('assignedUser')
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('subject', 'like', "%{$v}%"))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['assigned_to'] ?? null, fn ($q, $v) => $q->where('assigned_to', $v))
            ->when($filters['related_to_type'] ?? null, fn ($q, $v) => $q->where('related_to_type', $v))
            ->when($filters['related_to_id'] ?? null, fn ($q, $v) => $q->where('related_to_id', $v))
            ->latest('due_date')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CrmActivity
    {
        return CrmActivity::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CrmActivity $activity, array $data): CrmActivity
    {
        $activity->update($data);

        return $activity->fresh();
    }

    /**
     * Mark an activity as completed.
     */
    public function complete(CrmActivity $activity): CrmActivity
    {
        $activity->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $activity;
    }

    /**
     * Cancel an activity.
     */
    public function cancel(CrmActivity $activity): CrmActivity
    {
        $activity->update([
            'status' => 'cancelled',
        ]);

        return $activity;
    }

    /**
     * Get overdue activities.
     */
    public function getOverdue(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return CrmActivity::query()
            ->with('assignedUser')
            ->pending()
            ->overdue()
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }
}
