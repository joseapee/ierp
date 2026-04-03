<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Livewire\Catalog\UnitConversionFormModal;
use App\Livewire\Catalog\UnitConversionList;
use App\Models\Tenant;
use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnitConversionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->get(route('units.conversions'))->assertRedirect(route('login'));
    }

    public function test_renders_conversion_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UnitConversionList::class)
            ->assertStatus(200)
            ->assertSee('Unit Conversions');
    }

    public function test_displays_existing_conversions(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['name' => 'Kilogram', 'abbreviation' => 'kg', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['name' => 'Gram', 'abbreviation' => 'g', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);

        UnitConversion::factory()->create([
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => 1000,
            'tenant_id' => $this->tenant->id,
        ]);

        Livewire::test(UnitConversionList::class)
            ->assertSee('Kilogram')
            ->assertSee('Gram')
            ->assertSee('1,000');
    }

    public function test_search_filters_conversions(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['name' => 'Kilogram', 'abbreviation' => 'kg', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['name' => 'Gram', 'abbreviation' => 'g', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);
        $m = UnitOfMeasure::factory()->create(['name' => 'Meter', 'abbreviation' => 'm', 'type' => 'length', 'tenant_id' => $this->tenant->id]);
        $cm = UnitOfMeasure::factory()->create(['name' => 'Centimeter', 'abbreviation' => 'cm', 'type' => 'length', 'tenant_id' => $this->tenant->id]);

        UnitConversion::factory()->create(['from_unit_id' => $kg->id, 'to_unit_id' => $g->id, 'factor' => 1000, 'tenant_id' => $this->tenant->id]);
        UnitConversion::factory()->create(['from_unit_id' => $m->id, 'to_unit_id' => $cm->id, 'factor' => 100, 'tenant_id' => $this->tenant->id]);

        Livewire::test(UnitConversionList::class)
            ->set('search', 'Kilogram')
            ->assertSee('Kilogram')
            ->assertDontSee('Meter');
    }

    public function test_type_filter_works(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['name' => 'Kilogram', 'abbreviation' => 'kg', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['name' => 'Gram', 'abbreviation' => 'g', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);
        $m = UnitOfMeasure::factory()->create(['name' => 'Meter', 'abbreviation' => 'm', 'type' => 'length', 'tenant_id' => $this->tenant->id]);
        $cm = UnitOfMeasure::factory()->create(['name' => 'Centimeter', 'abbreviation' => 'cm', 'type' => 'length', 'tenant_id' => $this->tenant->id]);

        UnitConversion::factory()->create(['from_unit_id' => $kg->id, 'to_unit_id' => $g->id, 'factor' => 1000, 'tenant_id' => $this->tenant->id]);
        UnitConversion::factory()->create(['from_unit_id' => $m->id, 'to_unit_id' => $cm->id, 'factor' => 100, 'tenant_id' => $this->tenant->id]);

        Livewire::test(UnitConversionList::class)
            ->set('typeFilter', 'weight')
            ->assertSee('Kilogram')
            ->assertDontSee('Meter');
    }

    public function test_delete_conversion_dispatches_toast(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);

        $conversion = UnitConversion::factory()->create([
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => 1000,
            'tenant_id' => $this->tenant->id,
        ]);

        Livewire::test(UnitConversionList::class)
            ->call('deleteConversion', $conversion->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('unit_conversions', ['id' => $conversion->id]);
    }

    public function test_create_conversion_with_valid_data(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['name' => 'Kilogram', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['name' => 'Gram', 'type' => 'weight', 'tenant_id' => $this->tenant->id]);

        Livewire::test(UnitConversionFormModal::class)
            ->call('open')
            ->assertSet('showModal', true)
            ->set('from_unit_id', (string) $kg->id)
            ->set('to_unit_id', (string) $g->id)
            ->set('factor', '1000')
            ->call('save')
            ->assertDispatched('conversionSaved')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('unit_conversions', [
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => 1000,
        ]);
    }

    public function test_edit_conversion_loads_existing_data(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);

        $conversion = UnitConversion::factory()->create([
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => 1000,
            'tenant_id' => $this->tenant->id,
        ]);

        Livewire::test(UnitConversionFormModal::class)
            ->call('open', $conversion->id)
            ->assertSet('from_unit_id', (string) $kg->id)
            ->assertSet('to_unit_id', (string) $g->id)
            ->assertSet('factor', '1000.0000000000')
            ->assertSet('conversionId', $conversion->id);
    }

    public function test_update_conversion_with_valid_data(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);

        $conversion = UnitConversion::factory()->create([
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => 1000,
            'tenant_id' => $this->tenant->id,
        ]);

        Livewire::test(UnitConversionFormModal::class)
            ->call('open', $conversion->id)
            ->set('factor', '500')
            ->call('save')
            ->assertDispatched('conversionSaved')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('unit_conversions', [
            'id' => $conversion->id,
            'factor' => 500,
        ]);
    }

    public function test_validation_fails_with_same_from_and_to_unit(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(UnitConversionFormModal::class)
            ->call('open')
            ->set('from_unit_id', (string) $kg->id)
            ->set('to_unit_id', (string) $kg->id)
            ->set('factor', '1')
            ->call('save')
            ->assertHasErrors(['to_unit_id']);
    }

    public function test_validation_fails_without_factor(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(UnitConversionFormModal::class)
            ->call('open')
            ->set('from_unit_id', (string) $kg->id)
            ->set('to_unit_id', (string) $g->id)
            ->set('factor', '')
            ->call('save')
            ->assertHasErrors(['factor']);
    }

    public function test_validation_fails_with_zero_factor(): void
    {
        $this->actingAs($this->admin);

        $kg = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);
        $g = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(UnitConversionFormModal::class)
            ->call('open')
            ->set('from_unit_id', (string) $kg->id)
            ->set('to_unit_id', (string) $g->id)
            ->set('factor', '0')
            ->call('save')
            ->assertHasErrors(['factor']);
    }

    public function test_validation_fails_without_from_unit(): void
    {
        $this->actingAs($this->admin);

        $g = UnitOfMeasure::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(UnitConversionFormModal::class)
            ->call('open')
            ->set('from_unit_id', '')
            ->set('to_unit_id', (string) $g->id)
            ->set('factor', '1000')
            ->call('save')
            ->assertHasErrors(['from_unit_id']);
    }

    public function test_modal_resets_on_open(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UnitConversionFormModal::class)
            ->set('from_unit_id', '999')
            ->set('to_unit_id', '888')
            ->set('factor', '123')
            ->call('open')
            ->assertSet('from_unit_id', '')
            ->assertSet('to_unit_id', '')
            ->assertSet('factor', '')
            ->assertSet('conversionId', null)
            ->assertSet('showModal', true);
    }
}
