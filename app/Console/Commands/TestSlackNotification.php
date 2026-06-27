<?php

namespace App\Console\Commands;

use App\Models\Todo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('app:test-slack-notification')]
#[Description('Slackへ期限が近いTodoを通知する')]
class TestSlackNotification extends Command
{
    public function handle()
    {
        $todos = Todo::whereNotNull('deadline_at')
            ->whereNull('slack_notified_at')
            ->whereBetween('deadline_at', [now(), now()->addDay()])
            ->get();

        foreach ($todos as $todo) {
            $response = Http::post(env('SLACK_WEBHOOK_URL'), [
                'text' => "⏰ 期限が近づいています。\n\nタイトル: {$todo->content}\n期限: {$todo->deadline_at}",
            ]);

            if ($response->successful()) {
                $todo->update([
                    'slack_notified_at' => now(),
                ]);
            }
        }

        $this->info("{$todos->count()}件のTodoを通知しました。");

        return self::SUCCESS;
    }
}
