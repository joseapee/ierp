<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CrmContact;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LeadService
{
    /**
     * Paginated lead listing with filters.
     *
     * @param  array{search?: string, status?: string, source?: string, assigned_to?: int|string|null}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Lead::query()
            ->with('assignedUser')
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q
                ->where('lead_name', 'like', "%{$v}%")
                ->orWhere('company_name', 'like', "%{$v}%")
                ->orWhere('email', 'like', "%{$v}%")
            ))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['source'] ?? null, fn ($q, $v) => $q->where('source', $v))
            ->when($filters['assigned_to'] ?? null, fn ($q, $v) => $q->where('assigned_to', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Lead
    {
        return Lead::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->fresh();
    }

    /**
     * Convert a lead to a Customer (and optionally a CrmContact).
     */
    public function convert(Lead $lead, bool $createContact = true): Customer
    {
        if ($lead->status === 'converted') {
            throw new RuntimeException('Lead is already converted.');
        }

        return DB::transaction(function () use ($lead, $createContact): Customer {
            $customer = Customer::create([
                'tenant_id' => $lead->tenant_id,
                'name' => $lead->company_name ?: $lead->lead_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'notes' => $lead->notes,
                'is_active' => true,
            ]);

            if ($createContact && $lead->lead_name) {
                $names = explode(' ', $lead->lead_name, 2);
                CrmContact::create([
                    'tenant_id' => $lead->tenant_id,
                    'customer_id' => $customer->id,
                    'first_name' => $names[0],
                    'last_name' => $names[1] ?? '',
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'is_primary' => true,
                ]);
            }

            $lead->update([
                'status' => 'converted',
                'converted_customer_id' => $customer->id,
                'converted_at' => now(),
            ]);

            return $customer;
        });
    }

    /**
     * Mark a lead as lost.
     */
    public function markLost(Lead $lead, ?string $reason = null): Lead
    {
        if ($lead->status === 'converted') {
            throw new RuntimeException('Cannot mark a converted lead as lost.');
        }

        $notes = $lead->notes;
        if ($reason) {
            $notes = ($notes ? $notes."\n\n" : '').'Lost Reason: '.$reason;
        }

        $lead->update([
            'status' => 'lost',
            'notes' => $notes,
        ]);

        return $lead;
    }

    /**
     * Advance or change lead status with transition validation.
     */
    public function updateStatus(Lead $lead, string $status): Lead
    {
        /** @var array<string, array<int, string>> */
        $validTransitions = [
            'new' => ['contacted', 'qualified', 'lost'],
            'contacted' => ['qualified', 'proposal_sent', 'lost'],
            'qualified' => ['proposal_sent', 'negotiation', 'lost'],
            'proposal_sent' => ['negotiation', 'converted', 'lost'],
            'negotiation' => ['converted', 'lost'],
        ];

        if (! isset($validTransitions[$lead->status]) || ! in_array($status, $validTransitions[$lead->status])) {
            throw new RuntimeException("Invalid status transition from {$lead->status} to {$status}.");
        }

        $lead->update(['status' => $status]);

        return $lead;
    }
}
