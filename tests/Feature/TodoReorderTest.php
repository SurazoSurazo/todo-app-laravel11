<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_todos_can_be_reordered(): void
    {
        $category = Category::create(['name' => '仕事']);
        $firstTodo = Todo::create([
            'category_id' => $category->id,
            'content' => 'first',
            'sort_order' => 1,
        ]);
        $secondTodo = Todo::create([
            'category_id' => $category->id,
            'content' => 'second',
            'sort_order' => 2,
        ]);

        $response = $this->patchJson('/todos/reorder', [
            'todo_ids' => [$secondTodo->id, $firstTodo->id],
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Todoの並び順を更新しました']);
        $this->assertDatabaseHas('todos', [
            'id' => $secondTodo->id,
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('todos', [
            'id' => $firstTodo->id,
            'sort_order' => 2,
        ]);
    }
}
