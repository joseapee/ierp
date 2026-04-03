<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyFormatterTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create([
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
        ]);
        app()->instance('current.tenant', $this->tenant);
    }

    public function test_format_currency_with_ngn(): void
    {
        $this->assertEquals('₦1,000.00', format_currency(1000));
    }

    public function test_format_currency_with_usd(): void
    {
        $this->tenant->update(['currency' => 'USD']);

        $this->assertEquals('$1,000.00', format_currency(1000));
    }

    public function test_format_currency_with_eur(): void
    {
        $this->tenant->update(['currency' => 'EUR']);

        $this->assertEquals('€2,500.50', format_currency(2500.50));
    }

    public function test_format_currency_with_custom_decimals(): void
    {
        $this->assertEquals('₦1,000', format_currency(1000, 0));
        $this->assertEquals('₦1,000.5000', format_currency(1000.5, 4));
    }

    public function test_format_currency_with_null_amount(): void
    {
        $this->assertEquals('₦0.00', format_currency(null));
    }

    public function test_format_currency_with_zero(): void
    {
        $this->assertEquals('₦0.00', format_currency(0));
    }

    public function test_format_currency_with_string_amount(): void
    {
        $this->assertEquals('₦1,234.56', format_currency('1234.56'));
    }

    public function test_format_currency_with_negative_amount(): void
    {
        $this->assertEquals('₦-500.00', format_currency(-500));
    }

    public function test_format_money_without_symbol(): void
    {
        $this->assertEquals('1,000.00', format_money(1000));
    }

    public function test_format_money_with_custom_decimals(): void
    {
        $this->assertEquals('1,000.5000', format_money(1000.5, 4));
    }

    public function test_currency_symbol_returns_correct_symbols(): void
    {
        $this->assertEquals('₦', currency_symbol('NGN'));
        $this->assertEquals('$', currency_symbol('USD'));
        $this->assertEquals('€', currency_symbol('EUR'));
        $this->assertEquals('£', currency_symbol('GBP'));
        $this->assertEquals('₹', currency_symbol('INR'));
        $this->assertEquals('R', currency_symbol('ZAR'));
    }

    public function test_currency_symbol_unknown_code_returns_code(): void
    {
        $this->assertEquals('XYZ ', currency_symbol('XYZ'));
    }

    public function test_tenant_currency_returns_tenant_currency(): void
    {
        $this->assertEquals('NGN', tenant_currency());

        $this->tenant->update(['currency' => 'USD']);
        $this->assertEquals('USD', tenant_currency());
    }

    public function test_tenant_currency_defaults_to_ngn_without_tenant(): void
    {
        app()->forgetInstance('current.tenant');

        $this->assertEquals('NGN', tenant_currency());
    }

    public function test_tenant_timezone_returns_tenant_timezone(): void
    {
        $this->assertEquals('Africa/Lagos', tenant_timezone());

        $this->tenant->update(['timezone' => 'America/New_York']);
        $this->assertEquals('America/New_York', tenant_timezone());
    }

    public function test_tenant_timezone_defaults_to_lagos_without_tenant(): void
    {
        app()->forgetInstance('current.tenant');

        $this->assertEquals('Africa/Lagos', tenant_timezone());
    }

    public function test_format_date_converts_to_tenant_timezone(): void
    {
        $this->tenant->update(['timezone' => 'America/New_York']);

        // Midnight UTC on Jan 2 is still Jan 1 in New York (EST = UTC-5)
        $date = Carbon::parse('2025-01-02 00:00:00', 'UTC');

        $this->assertEquals('2025-01-01', format_date($date));
    }

    public function test_format_date_with_custom_format(): void
    {
        $date = Carbon::parse('2025-06-15 10:00:00', 'UTC');

        $this->assertEquals('15 Jun 2025', format_date($date, 'd M Y'));
    }

    public function test_format_date_returns_dash_for_null(): void
    {
        $this->assertEquals('—', format_date(null));
    }

    public function test_format_date_accepts_string_input(): void
    {
        $this->assertEquals('2025-06-15', format_date('2025-06-15 10:00:00'));
    }

    public function test_format_datetime_converts_to_tenant_timezone(): void
    {
        $this->tenant->update(['timezone' => 'Asia/Tokyo']); // UTC+9

        $date = Carbon::parse('2025-01-01 15:00:00', 'UTC');

        $this->assertEquals('2025-01-02 00:00', format_datetime($date));
    }

    public function test_format_datetime_with_custom_format(): void
    {
        $date = Carbon::parse('2025-06-15 14:30:00', 'UTC');

        $this->assertEquals('15 Jun 2025 15:30', format_datetime($date, 'd M Y H:i'));
    }

    public function test_format_datetime_returns_dash_for_null(): void
    {
        $this->assertEquals('—', format_datetime(null));
    }

    public function test_format_currency_without_tenant_uses_ngn(): void
    {
        app()->forgetInstance('current.tenant');

        $this->assertEquals('₦1,000.00', format_currency(1000));
    }
}
