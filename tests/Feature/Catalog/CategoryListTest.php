<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Livewire\Catalog\CategoryList;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryListTest extends TestCase
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
        $this->get(route('categories.index'))->assertRedirect(route('login'));
    }

    public function test_renders_category_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CategoryList::class)
            ->assertStatus(200)
            ->assertSee('Categories');
    }

    public function test_search_filters_categories(): void
    {
        $this->actingAs($this->admin);

        Category::factory()->create(['name' => 'Electronics', 'tenant_id' => $this->tenant->id]);
        Category::factory()->create(['name' => 'Fashion', 'tenant_id' => $this->tenant->id]);

        Livewire::test(CategoryList::class)
            ->set('search', 'Electro')
            ->assertSee('Electronics')
            ->assertDontSee('Fashion');
    }

    public function test_delete_category_dispatches_toast(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(CategoryList::class)
            ->call('deleteCategory', $category->id)
            ->assertDispatched('toast');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
