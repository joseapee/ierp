<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int $fiscal_year_id
 * @property string $entry_number
 * @property \Illuminate\Support\Carbon $date
 * @property string $description
 * @property string|null $reference
 * @property string|null $source_type
 * @property int|null $source_id
 * @property string $status
 * @property int|null $posted_by
 * @property \Illuminate\Support\Carbon|null $posted_at
 * @property int|null $voided_by
 * @property \Illuminate\Support\Carbon|null $voided_at
 * @property string|null $notes
 */
class JournalEntry extends Model
{
    /** @use HasFactory<\Database\Factories\JournalEntryFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'fiscal_year_id',
        'entry_number',
        'date',
        'description',
        'reference',
        'source_type',
        'source_id',
        'status',
        'posted_by',
        'posted_at',
        'voided_by',
        'voided_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'posted_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function postedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function getIsBalancedAttribute(): bool
    {
        $totals = $this->lines()->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

        return $totals && bccomp((string) ($totals->total_debit ?? 0), (string) ($totals->total_credit ?? 0), 4) === 0;
    }

    public function getTotalDebitAttribute(): string
    {
        return (string) ($this->lines()->sum('debit') ?? 0);
    }

    public function getTotalCreditAttribute(): string
    {
        return (string) ($this->lines()->sum('credit') ?? 0);
    }
}
