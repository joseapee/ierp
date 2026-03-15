<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CrmCommunication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CrmCommunicationService
{
    /**
     * Paginated communication listing with filters.
     *
     * @param  array{search?: string, type?: string, customer_id?: int|string|null, lead_id?: int|string|null}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CrmCommunication::query()
            ->with(['customer', 'contact', 'lead', 'creator'])
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('subject', 'like', "%{$v}%"))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))
            ->when($filters['lead_id'] ?? null, fn ($q, $v) => $q->where('lead_id', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CrmCommunication
    {
        return CrmCommunication::create($data);
    }

    /**
     * Get communication timeline for a specific customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, CrmCommunication>
     */
    public function getCustomerTimeline(int $customerId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return CrmCommunication::query()
            ->with(['contact', 'creator'])
            ->where('customer_id', $customerId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get communication timeline for a specific lead.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, CrmCommunication>
     */
    public function getLeadTimeline(int $leadId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return CrmCommunication::query()
            ->with(['contact', 'creator'])
            ->where('lead_id', $leadId)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
