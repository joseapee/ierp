<?php

declare(strict_types=1);

use App\Models\Tenant;
use Carbon\Carbon;

if (! function_exists('format_currency')) {
    /**
     * Format a numeric value as currency using the current tenant's currency setting.
     */
    function format_currency(float|int|string|null $amount, int $decimals = 2): string
    {
        $amount = (float) ($amount ?? 0);
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;
        $currencyCode = $tenant?->currency ?? 'NGN';
        $symbol = currency_symbol($currencyCode);

        return $symbol.number_format($amount, $decimals, '.', ',');
    }
}

if (! function_exists('format_money')) {
    /**
     * Format a numeric value as money without the currency symbol (just formatted number).
     */
    function format_money(float|int|string|null $amount, int $decimals = 2): string
    {
        return number_format((float) ($amount ?? 0), $decimals, '.', ',');
    }
}

if (! function_exists('currency_symbol')) {
    /**
     * Get the symbol for a given ISO 4217 currency code.
     */
    function currency_symbol(?string $code = null): string
    {
        $code = $code ?? 'NGN';

        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'KES' => 'KSh',
            'GHS' => 'GH₵',
            'ZAR' => 'R',
            'CAD' => 'CA$',
            'AUD' => 'A$',
            'BRL' => 'R$',
            'AED' => 'د.إ',
            'SAR' => '﷼',
            'EGP' => 'E£',
            'XOF' => 'CFA',
            'XAF' => 'FCFA',
            'TZS' => 'TSh',
            'UGX' => 'USh',
            'RWF' => 'RF',
            'ETB' => 'Br',
            'MAD' => 'MAD',
        ];

        return $symbols[strtoupper($code)] ?? $code.' ';
    }
}

if (! function_exists('tenant_currency')) {
    /**
     * Get the current tenant's currency code.
     */
    function tenant_currency(): string
    {
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        return $tenant?->currency ?? 'NGN';
    }
}

if (! function_exists('tenant_timezone')) {
    /**
     * Get the current tenant's timezone.
     */
    function tenant_timezone(): string
    {
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        return $tenant?->timezone ?? 'Africa/Lagos';
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date value in the tenant's timezone.
     *
     * @param  Carbon|Illuminate\Support\Carbon|string|null  $date
     */
    function format_date(mixed $date, string $format = 'Y-m-d'): string
    {
        if ($date === null) {
            return '—';
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->setTimezone(tenant_timezone())->format($format);
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Format a datetime value in the tenant's timezone.
     *
     * @param  Carbon|Illuminate\Support\Carbon|string|null  $date
     */
    function format_datetime(mixed $date, string $format = 'Y-m-d H:i'): string
    {
        if ($date === null) {
            return '—';
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->setTimezone(tenant_timezone())->format($format);
    }
}
