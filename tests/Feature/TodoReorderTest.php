<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_todos_can_be_reordered(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);
        $firstTodo = Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => 'first',
            'sort_order' => 1,
        ]);
        $secondTodo = Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => 'second',
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($user)->patchJson('/todos/reorder', [
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
