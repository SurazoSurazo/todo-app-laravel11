<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())->get();

        return view('category', compact('categories'));
    }

    public function store(CategoryRequest $request)
    {
        $category = $request->only(['name']);
        $category['user_id'] = Auth::id();

        Category::create($category);

        return redirect('/categories')->with('message', 'カテゴリを作成しました');
    }

    public function update(CategoryRequest $request)
    {
        $category = $request->only(['name']);
        Category::where('user_id', Auth::id())->findOrFail($request->id)->update($category);

        return redirect('/categories')->with('message', 'カテゴリを更新しました');
    }

    public function destroy(Request $request)
    {
        $category = Category::where('user_id', Auth::id())->findOrFail($request->id);

        Todo::where('user_id', Auth::id())
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        $category->delete();

        return redirect('/categories')->with('message', 'カテゴリを削除しました');
    }
}
