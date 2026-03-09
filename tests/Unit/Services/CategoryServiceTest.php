<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Tenant;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $service;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CategoryService;
        $this->tenant = Tenant::factory()->create();
        app()->instance('current.tenant', $this->tenant);
    }

    public function test_tree_returns_hierarchical_categories(): void
    {
        $parent = Category::factory()->create(['tenant_id' => $this->tenant->id, 'parent_id' => null]);
        $child = Category::factory()->create(['tenant_id' => $this->tenant->id, 'parent_id' => $parent->id]);

        $tree = $this->service->tree();

        $this->assertCount(1, $tree);
        $this->assertEquals($parent->id, $tree->first()->id);
        $this->assertCount(1, $tree->first()->children);
    }

    public function test_create_category(): void
    {
        $category = $this->service->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic products',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_update_category(): void
    {
        $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

        $updated = $this->service->update($category, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_delete_category(): void
    {
        $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

        $result = $this->service->delete($category);

        $this->assertTrue($result);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_children(): void
    {
        $parent = Category::factory()->create(['tenant_id' => $this->tenant->id]);
        Category::factory()->create(['tenant_id' => $this->tenant->id, 'parent_id' => $parent->id]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot delete a category that has children.');

        $this->service->delete($parent);
    }
}
