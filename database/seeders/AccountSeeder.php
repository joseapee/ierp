<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Default Chart of Accounts template.
     *
     * @var array<string, array<int, array{code: string, name: string, sub_type: string|null, children?: array<int, array{code: string, name: string, sub_type: string|null}>}>>
     */
    protected array $accounts = [
        'asset' => [
            ['code' => '1000', 'name' => 'Cash', 'sub_type' => 'current_asset'],
            ['code' => '1100', 'name' => 'Bank', 'sub_type' => 'current_asset'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'sub_type' => 'current_asset'],
            ['code' => '1300', 'name' => 'Inventory', 'sub_type' => 'current_asset', 'children' => [
                ['code' => '1310', 'name' => 'Raw Materials', 'sub_type' => 'current_asset'],
                ['code' => '1320', 'name' => 'Work in Progress', 'sub_type' => 'current_asset'],
                ['code' => '1330', 'name' => 'Finished Goods', 'sub_type' => 'current_asset'],
            ]],
            ['code' => '1500', 'name' => 'Fixed Assets', 'sub_type' => 'fixed_asset'],
            ['code' => '1510', 'name' => 'Accumulated Depreciation', 'sub_type' => 'contra_asset'],
        ],
        'liability' => [
            ['code' => '2000', 'name' => 'Accounts Payable', 'sub_type' => 'current_liability'],
            ['code' => '2100', 'name' => 'VAT Payable', 'sub_type' => 'current_liability'],
            ['code' => '2200', 'name' => 'Accrued Expenses', 'sub_type' => 'current_liability'],
            ['code' => '2300', 'name' => 'Short-term Loans', 'sub_type' => 'current_liability'],
        ],
        'equity' => [
            ['code' => '3000', 'name' => "Owner's Equity", 'sub_type' => null],
            ['code' => '3100', 'name' => 'Retained Earnings', 'sub_type' => null],
        ],
        'revenue' => [
            ['code' => '4000', 'name' => 'Sales Revenue', 'sub_type' => null],
            ['code' => '4100', 'name' => 'Service Revenue', 'sub_type' => null],
            ['code' => '4200', 'name' => 'Other Income', 'sub_type' => null],
        ],
        'expense' => [
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'sub_type' => 'cogs'],
            ['code' => '5100', 'name' => 'Raw Material Cost', 'sub_type' => 'cogs'],
            ['code' => '5200', 'name' => 'Direct Labor', 'sub_type' => 'cogs'],
            ['code' => '5300', 'name' => 'Manufacturing Overhead', 'sub_type' => 'cogs'],
            ['code' => '6000', 'name' => 'Salaries & Wages', 'sub_type' => 'operating'],
            ['code' => '6100', 'name' => 'Rent Expense', 'sub_type' => 'operating'],
            ['code' => '6200', 'name' => 'Utilities Expense', 'sub_type' => 'operating'],
            ['code' => '6300', 'name' => 'Office Supplies', 'sub_type' => 'operating'],
            ['code' => '6400', 'name' => 'Depreciation Expense', 'sub_type' => 'operating'],
            ['code' => '6500', 'name' => 'Bank Charges', 'sub_type' => 'operating'],
        ],
    ];

    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if (! $tenant) {
            return;
        }

        foreach ($this->accounts as $type => $accounts) {
            $normalBalance = in_array($type, ['asset', 'expense']) ? 'debit' : 'credit';

            foreach ($accounts as $accountData) {
                $children = $accountData['children'] ?? [];
                unset($accountData['children']);

                $parent = Account::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $accountData['code']],
                    [
                        'name' => $accountData['name'],
                        'type' => $type,
                        'sub_type' => $accountData['sub_type'],
                        'normal_balance' => $normalBalance,
                        'is_system' => true,
                        'is_active' => true,
                    ]
                );

                foreach ($children as $childData) {
                    Account::query()->updateOrCreate(
                        ['tenant_id' => $tenant->id, 'code' => $childData['code']],
                        [
                            'parent_id' => $parent->id,
                            'name' => $childData['name'],
                            'type' => $type,
                            'sub_type' => $childData['sub_type'],
                            'normal_balance' => $normalBalance,
                            'is_system' => true,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // Seed a default fiscal year for the current year.
        $year = (int) date('Y');
        FiscalYear::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => "FY {$year}"],
            [
                'start_date' => "{$year}-01-01",
                'end_date' => "{$year}-12-31",
                'status' => 'open',
            ]
        );
    }
}
