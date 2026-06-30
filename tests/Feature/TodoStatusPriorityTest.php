<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoStatusPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_todo_is_created_with_default_status_and_priority(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);

        $response = $this->actingAs($user)->post('/todos', [
            'category_id' => $category->id,
            'content' => '資料作成',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '資料作成',
            'status' => Todo::STATUS_NOT_STARTED,
            'priority' => Todo::PRIORITY_MEDIUM,
        ]);
    }

    public function test_todo_status_and_priority_can_be_created_and_updated(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);

        $this->actingAs($user)->post('/todos', [
            'category_id' => $category->id,
            'content' => 'レビュー',
            'status' => Todo::STATUS_IN_PROGRESS,
            'priority' => Todo::PRIORITY_HIGH,
        ])->assertRedirect('/');

        $todo = Todo::where('user_id', $user->id)->firstOrFail();

        $this->assertSame(Todo::STATUS_IN_PROGRESS, $todo->status);
        $this->assertSame(Todo::PRIORITY_HIGH, $todo->priority);

        $response = $this->actingAs($user)->patch('/todos/update', [
            'id' => $todo->id,
            'content' => 'レビュー完了',
            'status' => Todo::STATUS_DONE,
            'priority' => Todo::PRIORITY_LOW,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'content' => 'レビュー完了',
            'status' => Todo::STATUS_DONE,
            'priority' => Todo::PRIORITY_LOW,
        ]);
    }

    public function test_todo_status_and_priority_must_be_valid_values(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);

        $response = $this->actingAs($user)->from('/')->post('/todos', [
            'category_id' => $category->id,
            'content' => '資料作成',
            'status' => '未着手',
            'priority' => '最優先',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['status', 'priority']);
        $this->assertDatabaseMissing('todos', [
            'user_id' => $user->id,
            'content' => '資料作成',
        ]);
    }
}
