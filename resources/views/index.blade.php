@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="todo__alert">
  @if (session('message'))
  <div class="todo__alert--success">
    {{ session('message') }}
  </div>
  @endif
  @if ($errors->any())
  <div class="todo__alert--danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
</div>

<div class="todo__content">
  <div class="section__title">
    <h2>新規作成</h2>
  </div>
  <form class="create-form" action="/todos" method="post">
    @csrf
    <div class="create-form__item">
      <input class="create-form__item-input" type="text" name="content" value="{{ old('content') }}">
      <select class="create-form__item-select" name="category_id">
        <option value="">カテゴリ</option>
        @foreach ($categories as $category)
        <option value="{{ $category['id'] }}" @if (old('category_id') == $category['id']) selected @endif>{{ $category['name'] }}</option>
        @endforeach
      </select>
    </div>
    <div class="create-form__button">
      <button class="create-form__button-submit" type="submit">作成</button>
    </div>
  </form>

  <div class="section__title">
    <h2>Todo検索</h2>
  </div>
  <form class="search-form" action="/todos/search" method="get">
    <div class="search-form__item">
      <input class="search-form__item-input" type="text" name="keyword" value="{{ request('keyword') }}">
      <select class="search-form__item-select" name="category_id">
        <option value="">カテゴリ</option>
        @foreach ($categories as $category)
        <option value="{{ $category['id'] }}" @if (request('category_id') == $category['id']) selected @endif>{{ $category['name'] }}</option>
        @endforeach
      </select>
    </div>
    <div class="search-form__button">
      <button class="search-form__button-submit" type="submit">検索</button>
    </div>
  </form>

  <div class="todo-table">
    <table class="todo-table__inner">
      <thead>
        <tr class="todo-table__row">
          <th class="todo-table__header todo-table__header--handle"></th>
          <th class="todo-table__header">
            <span class="todo-table__header-span">Todo</span>
            <span class="todo-table__header-span">カテゴリ</span>
          </th>
          <th class="todo-table__header todo-table__header--action"></th>
        </tr>
      </thead>

      <tbody id="sortable-todos">
      @foreach ($todos as $todo)
      <tr class="todo-table__row" data-todo-id="{{ $todo['id'] }}">
        <td class="todo-table__item todo-table__item--handle">
          <button class="todo-table__drag-handle" type="button" draggable="true" title="ドラッグして並び替え">↕</button>
        </td>
        <td class="todo-table__item">
          <form class="update-form" action="/todos/update" method="post">
            @method('PATCH')
            @csrf
            <div class="update-form__item">
              <input class="update-form__item-input" type="text" name="content" value="{{ $todo['content'] }}">
              <input type="hidden" name="id" value="{{ $todo['id'] }}">
            </div>
            <div class="update-form__item">
              <p class="update-form__item-p">{{ $todo['category']['name'] }}</p>
            </div>
            <div class="update-form__button">
              <button class="update-form__button-submit" type="submit">更新</button>
            </div>
          </form>
        </td>
        <td class="todo-table__item">
          <form class="delete-form" action="/todos/delete" method="post">
            @method('DELETE')
            @csrf
            <div class="delete-form__button">
              <input type="hidden" name="id" value="{{ $todo['id'] }}">
              <button class="delete-form__button-submit" type="submit">削除</button>
            </div>
          </form>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('scripts')
<script>
  const sortableTodos = document.getElementById('sortable-todos');
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
  let draggingRow = null;

  const saveTodoOrder = () => {
    const todoIds = [...sortableTodos.querySelectorAll('[data-todo-id]')].map((row) => row.dataset.todoId);

    fetch('/todos/reorder', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ todo_ids: todoIds }),
    }).catch(() => {
      window.location.reload();
    });
  };

  sortableTodos.addEventListener('dragstart', (event) => {
    if (!event.target.classList.contains('todo-table__drag-handle')) {
      event.preventDefault();
      return;
    }

    draggingRow = event.target.closest('.todo-table__row');
    draggingRow.classList.add('todo-table__row--dragging');
    event.dataTransfer.effectAllowed = 'move';
  });

  sortableTodos.addEventListener('dragover', (event) => {
    event.preventDefault();
    const targetRow = event.target.closest('.todo-table__row');

    if (!draggingRow || !targetRow || targetRow === draggingRow) {
      return;
    }

    const targetRect = targetRow.getBoundingClientRect();
    const insertAfter = event.clientY > targetRect.top + targetRect.height / 2;
    sortableTodos.insertBefore(draggingRow, insertAfter ? targetRow.nextSibling : targetRow);
  });

  sortableTodos.addEventListener('dragend', () => {
    if (!draggingRow) {
      return;
    }

    draggingRow.classList.remove('todo-table__row--dragging');
    draggingRow = null;
    saveTodoOrder();
  });
</script>
@endsection
