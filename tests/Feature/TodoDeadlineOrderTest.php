<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoDeadlineOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_todos_are_displayed_by_nearest_deadline(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);

        Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '来週のタスク',
            'deadline_at' => '2026-07-05 10:00:00',
            'sort_order' => 1,
        ]);
        Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '今日のタスク',
            'deadline_at' => '2026-06-28 18:00:00',
            'sort_order' => 2,
        ]);
        Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '期限なしタスク',
            'deadline_at' => null,
            'sort_order' => 3,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSeeInOrder([
            '今日のタスク',
            '来週のタスク',
            '期限なしタスク',
        ]);
    }
}
