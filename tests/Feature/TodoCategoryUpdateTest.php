<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoCategoryUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_todo_category_can_be_updated(): void
    {
        $user = User::factory()->create();
        $oldCategory = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);
        $newCategory = Category::create([
            'user_id' => $user->id,
            'name' => '家事',
        ]);
        $todo = Todo::create([
            'user_id' => $user->id,
            'category_id' => $oldCategory->id,
            'content' => '資料作成',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->patch('/todos/update', [
            'id' => $todo->id,
            'category_id' => $newCategory->id,
            'content' => '資料作成',
            'status' => Todo::STATUS_NOT_STARTED,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'category_id' => $newCategory->id,
        ]);
    }
}
