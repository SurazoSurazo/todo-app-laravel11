<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoRequest;
use App\Models\Category;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Todo::with('category')->orderBy('sort_order')->orderBy('id')->get();
        $categories = Category::all();

        return view('index', compact('todos', 'categories'));
    }

    public function search(Request $request)
    {
        $todos = Todo::with('category')
            ->categorySearch($request->category_id)
            ->keywordSearch($request->keyword)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $categories = Category::all();

        return view('index', compact('todos', 'categories'));
    }

    public function store(TodoRequest $request)
    {
        $todo = $request->only([
            'category_id',
            'content',
            'deadline_at',
        ]);

        $todo['sort_order'] = Todo::max('sort_order') + 1;

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

        Todo::find($request->id)->update($todo);

        return redirect('/')->with('message', 'Todoを更新しました');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'todo_ids' => ['required', 'array'],
            'todo_ids.*' => ['integer', 'exists:todos,id'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['todo_ids'] as $index => $todoId) {
                Todo::where('id', $todoId)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return response()->json(['message' => 'Todoの並び順を更新しました']);
    }

    public function destroy(Request $request)
    {
        Todo::find($request->id)->delete();

        return redirect('/')->with('message', 'Todoを削除しました');
    }
}
