<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoRequest;
use App\Models\Category;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    public function index()
    {
        $todos = $this->orderByNearestDeadline(
            Todo::with('category')->where('user_id', Auth::id())
        )->get();
        $categories = $this->currentUserCategories();

        return view('index', compact('todos', 'categories'));
    }

    public function search(Request $request)
    {
        $todos = $this->orderByNearestDeadline(
            Todo::with('category')->where('user_id', Auth::id())
            ->categorySearch($request->category_id)
            ->keywordSearch($request->keyword)
        )->get();

        $categories = $this->currentUserCategories();

        return view('index', compact('todos', 'categories'));
    }

    public function store(TodoRequest $request)
    {
        $todo = $request->only([
            'category_id',
            'content',
            'deadline_at',
        ]);

        $todo['user_id'] = Auth::id();

        $todo['sort_order'] = Todo::where('user_id', Auth::id())->max('sort_order') + 1;

        Todo::create($todo);

        return redirect('/')->with('message', 'Todoを作成しました');
    }

    public function update(TodoRequest $request)
    {
        $todo = $request->only([
            'content',
            'deadline_at',
        ]);

        $todo['slack_notified_at'] = null;

        Todo::where('user_id', Auth::id())->findOrFail($request->id)->update($todo);

        return redirect('/')->with('message', 'Todoを更新しました');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'todo_ids' => ['required', 'array'],
            'todo_ids.*' => ['integer'],
        ]);

        $ownedTodoCount = Todo::where('user_id', Auth::id())
            ->whereIn('id', $validated['todo_ids'])
            ->count();

        if ($ownedTodoCount !== count(array_unique($validated['todo_ids']))) {
            return response()->json([
                'message' => '指定されたTodoが見つかりません',
            ], 422);
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['todo_ids'] as $index => $todoId) {
                Todo::where('user_id', Auth::id())
                    ->where('id', $todoId)
                    ->update([
                        'sort_order' => $index + 1,
                    ]);
            }
        });

        return response()->json(['message' => 'Todoの並び順を更新しました']);
    }

    public function destroy(Request $request)
    {
        Todo::where('user_id', Auth::id())->findOrFail($request->id)->delete();

        return redirect('/')->with('message', 'Todoを削除しました');
    }

    private function currentUserCategories()
    {
        return Category::where('user_id', Auth::id())->get();
    }

    private function orderByNearestDeadline($query)
    {
        return $query
            ->orderByRaw('deadline_at IS NULL')
            ->orderBy('deadline_at')
            ->orderBy('id');
    }
}
