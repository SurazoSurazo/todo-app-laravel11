<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_deleted_without_deleting_todos(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);
        $todo = Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '会議準備',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->delete('/categories/delete', [
            'id' => $category->id,
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'content' => '会議準備',
            'category_id' => null,
        ]);
    }
}
