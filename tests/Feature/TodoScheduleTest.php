<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TodoScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_todo_can_be_created_with_deadline_datetime(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);
        $deadlineDate = now()->addDays(7)->format('Y-m-d');

        $response = $this->actingAs($user)->post('/todos', [
            'category_id' => $category->id,
            'content' => '会議準備',
            'deadline_date' => $deadlineDate,
            'deadline_time' => '18:30',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '会議準備',
            'deadline_at' => $deadlineDate . ' 18:30:00',
        ]);
    }

    public function test_todo_deadline_datetime_can_be_updated(): void
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
        $deadlineDate = now()->addDays(8)->format('Y-m-d');

        $response = $this->actingAs($user)->patch('/todos/update', [
            'id' => $todo->id,
            'content' => '資料作成',
            'deadline_date' => $deadlineDate,
            'deadline_time' => '09:15',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'content' => '資料作成',
            'deadline_at' => $deadlineDate . ' 09:15:00',
        ]);
    }

    public function test_todo_deadline_datetime_can_be_cleared(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);
        $todo = Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '資料作成',
            'sort_order' => 1,
            'deadline_at' => now()->addDays(9)->format('Y-m-d') . ' 09:15:00',
        ]);

        $response = $this->actingAs($user)->patch('/todos/update', [
            'id' => $todo->id,
            'content' => '資料作成',
            'deadline_date' => '',
            'deadline_time' => '',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'deadline_at' => null,
        ]);
    }

    public function test_todo_deadline_cannot_be_created_with_past_date(): void
    {
        Carbon::setTestNow('2026-07-01 12:30:00');
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);

        $response = $this->actingAs($user)->from('/')->post('/todos', [
            'category_id' => $category->id,
            'content' => '会議準備',
            'deadline_date' => '2026-06-30',
            'deadline_time' => '18:30',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('deadline_date');
        $this->assertDatabaseMissing('todos', [
            'user_id' => $user->id,
            'content' => '会議準備',
        ]);
    }

    public function test_todo_deadline_cannot_be_updated_with_past_time_today(): void
    {
        Carbon::setTestNow('2026-07-01 12:30:00');
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => '仕事',
        ]);
        $todo = Todo::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'content' => '資料作成',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->from('/')->patch('/todos/update', [
            'id' => $todo->id,
            'content' => '資料作成',
            'deadline_date' => '2026-07-01',
            'deadline_time' => '12:29',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('deadline_time');
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'deadline_at' => null,
        ]);
    }

    public function test_slack_notification_uses_deadline_at(): void
    {
        Http::fake();
        config(['services.slack.webhook_url' => 'https://example.com/slack']);
        $category = Category::create(['name' => '仕事']);
        Todo::create([
            'category_id' => $category->id,
            'content' => '期限あり',
            'sort_order' => 1,
            'deadline_at' => now()->addHour(),
        ]);

        $this->artisan('app:test-slack-notification')
            ->assertExitCode(0);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return str_contains($request['text'], '期限あり');
        });
    }
}
