<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_current_users_categories_are_displayed(): void
    {
        [$daichi, $rin] = User::factory()->count(2)->create();

        Category::create([
            'user_id' => $daichi->id,
            'name' => 'Daichi用',
        ]);
        Category::create([
            'user_id' => $rin->id,
            'name' => 'Rin用',
        ]);

        $categoryResponse = $this->actingAs($rin)->get('/categories');
        $categoryResponse->assertOk();
        $categoryResponse->assertSee('Rin用');
        $categoryResponse->assertDontSee('Daichi用');

        $todoResponse = $this->actingAs($rin)->get('/');
        $todoResponse->assertOk();
        $todoResponse->assertSee('Rin用');
        $todoResponse->assertDontSee('Daichi用');

        $searchResponse = $this->actingAs($rin)->get('/todos/search');
        $searchResponse->assertOk();
        $searchResponse->assertSee('Rin用');
        $searchResponse->assertDontSee('Daichi用');
    }

    public function test_other_users_categories_cannot_be_updated_or_deleted(): void
    {
        [$daichi, $rin] = User::factory()->count(2)->create();
        $daichiCategory = Category::create([
            'user_id' => $daichi->id,
            'name' => 'Daichi用',
        ]);

        $this->actingAs($rin)->patch('/categories/update', [
            'id' => $daichiCategory->id,
            'name' => '更新できない',
        ])->assertNotFound();

        $this->actingAs($rin)->delete('/categories/delete', [
            'id' => $daichiCategory->id,
        ])->assertNotFound();

        $this->assertDatabaseHas('categories', [
            'id' => $daichiCategory->id,
            'name' => 'Daichi用',
        ]);
    }

    public function test_todo_cannot_use_other_users_category(): void
    {
        [$daichi, $rin] = User::factory()->count(2)->create();
        $daichiCategory = Category::create([
            'user_id' => $daichi->id,
            'name' => 'Daichi用',
        ]);

        $response = $this->actingAs($rin)->from('/')->post('/todos', [
            'category_id' => $daichiCategory->id,
            'content' => '他人のカテゴリ',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('todos', [
            'user_id' => $rin->id,
            'category_id' => $daichiCategory->id,
            'content' => '他人のカテゴリ',
        ]);
    }

    public function test_other_users_todos_cannot_be_updated_deleted_or_reordered(): void
    {
        [$daichi, $rin] = User::factory()->count(2)->create();
        $daichiCategory = Category::create([
            'user_id' => $daichi->id,
            'name' => 'Daichi用',
        ]);
        $rinCategory = Category::create([
            'user_id' => $rin->id,
            'name' => 'Rin用',
        ]);
        $todo = Todo::create([
            'user_id' => $daichi->id,
            'category_id' => $daichiCategory->id,
            'content' => 'DaichiのTodo',
            'sort_order' => 1,
        ]);

        $this->actingAs($rin)->patch('/todos/update', [
            'id' => $todo->id,
            'category_id' => $rinCategory->id,
            'content' => '更新できない',
        ])->assertNotFound();

        $this->actingAs($rin)->delete('/todos/delete', [
            'id' => $todo->id,
        ])->assertNotFound();

        $this->actingAs($rin)->patchJson('/todos/reorder', [
            'todo_ids' => [$todo->id],
        ])->assertUnprocessable();

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'content' => 'DaichiのTodo',
            'sort_order' => 1,
        ]);
    }
}
