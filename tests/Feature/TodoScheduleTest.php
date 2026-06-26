<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_todo_can_be_created_with_deadline_datetime(): void
    {
        $category = Category::create(['name' => '仕事']);

        $response = $this->post('/todos', [
            'category_id' => $category->id,
            'content' => '会議準備',
            'deadline_at' => '2026-06-27T18:30',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'category_id' => $category->id,
            'content' => '会議準備',
            'deadline_at' => '2026-06-27 18:30:00',
        ]);
    }

    public function test_todo_deadline_datetime_can_be_updated(): void
    {
        $category = Category::create(['name' => '仕事']);
        $todo = Todo::create([
            'category_id' => $category->id,
            'content' => '会議準備',
            'sort_order' => 1,
        ]);

        $response = $this->patch('/todos/update', [
            'id' => $todo->id,
            'content' => '資料作成',
            'deadline_at' => '2026-06-29T09:15',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'content' => '資料作成',
            'deadline_at' => '2026-06-29 09:15:00',
        ]);
    }
}
