<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int|null $subscription_id
 * @property int|null $payment_id
 * @property string $invoice_number
 * @property string $amount
 * @property string $currency
 * @property string $status
 * @property Carbon|null $issued_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $due_at
 * @property array|null $line_items
 */
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'payment_id',
        'invoice_number',
        'amount',
        'currency',
        'status',
        'issued_at',
        'paid_at',
        'due_at',
        'line_items',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'due_at' => 'datetime',
            'line_items' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
