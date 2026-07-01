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

@php
  $todoStatuses = \App\Models\Todo::STATUSES;
  $todoPriorities = \App\Models\Todo::PRIORITIES;
  $oldDeadline = old('deadline_at');
  $oldDeadlineDate = old('deadline_date') ?: ($oldDeadline && preg_match('/^\d{4}-\d{2}-\d{2}/', $oldDeadline) ? substr($oldDeadline, 0, 10) : '');
  $oldDeadlineTime = old('deadline_time') ?: ($oldDeadline && preg_match('/(?:T|\s)(\d{2}:\d{2})/', $oldDeadline, $matches) ? $matches[1] : '');
  $oldDeadlineLabel = $oldDeadlineDate ? str_replace('-', '/', $oldDeadlineDate) . ($oldDeadlineTime ? ' ' . $oldDeadlineTime : '') : '未設定';
@endphp

<div class="todo__content">
  <div class="section__title">
    <h2>新規作成</h2>
  </div>
  <form class="create-form" action="/todos" method="post">
    @csrf
    <div class="create-form__item">
      <input class="create-form__item-input" type="text" name="content" value="{{ old('content') }}" placeholder="Todo">
      <select class="create-form__item-select" name="category_id">
        <option value="">カテゴリ</option>
        @foreach ($categories as $category)
        <option value="{{ $category['id'] }}" @if (old('category_id') == $category['id']) selected @endif>{{ $category['name'] }}</option>
        @endforeach
      </select>
      <div class="create-form__deadline">
        <span class="create-form__deadline-label">期限</span>
        <input class="js-deadline-date" type="hidden" name="deadline_date" value="{{ $oldDeadlineDate }}">
        <input class="js-deadline-time" type="hidden" name="deadline_time" value="{{ $oldDeadlineTime }}">
        <button class="deadline-picker__button js-deadline-open" type="button">期限を選択</button>
        <span class="deadline-picker__value js-deadline-label">{{ $oldDeadlineLabel }}</span>
      </div>
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
          <th class="todo-table__header todo-table__header--main">
            <div class="todo-table__header-grid">
              <span class="todo-table__header-span">Todo</span>
              <span class="todo-table__header-span">カテゴリ</span>
              <span class="todo-table__header-span">状態</span>
              <span class="todo-table__header-span">優先度</span>
              <span class="todo-table__header-span">期限</span>
            </div>
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
              <p class="update-form__item-p">{{ $todo->category?->name ?? '未分類' }}</p>
            </div>
            <div class="update-form__item">
              <select class="update-form__item-select js-submit-on-enter" name="status">
                @foreach ($todoStatuses as $status)
                <option value="{{ $status }}" @if ($todo->status === $status) selected @endif>{{ $status }}</option>
                @endforeach
              </select>
            </div>
            <div class="update-form__item">
              <select class="update-form__item-select js-submit-on-enter" name="priority">
                @foreach ($todoPriorities as $priority)
                <option value="{{ $priority }}" @if ($todo->priority === $priority) selected @endif>{{ $priority }}</option>
                @endforeach
              </select>
            </div>
            <div class="update-form__item update-form__item--deadline">
              <input class="js-deadline-date" type="hidden" name="deadline_date" value="{{ $todo->deadline_at?->format('Y-m-d') }}">
              <input class="js-deadline-time" type="hidden" name="deadline_time" value="{{ $todo->deadline_at?->format('H:i') }}">
              <button class="deadline-picker__button js-deadline-open" type="button">期限を選択</button>
              <span class="deadline-picker__value js-deadline-label">{{ $todo->deadline_at ? $todo->deadline_at->format('Y/m/d H:i') : '未設定' }}</span>
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

<div class="calendar-modal" id="deadline-calendar" aria-hidden="true">
  <div class="calendar-modal__overlay js-calendar-close"></div>
  <form class="calendar-modal__content" role="dialog" aria-modal="true" aria-label="期限カレンダー">
    <div class="calendar-modal__header">
      <button class="calendar-modal__nav js-calendar-prev" type="button" aria-label="前の月">&lt;</button>
      <p class="calendar-modal__title js-calendar-title"></p>
      <button class="calendar-modal__nav js-calendar-next" type="button" aria-label="次の月">&gt;</button>
    </div>
    <div class="calendar-modal__week">
      <span>日</span>
      <span>月</span>
      <span>火</span>
      <span>水</span>
      <span>木</span>
      <span>金</span>
      <span>土</span>
    </div>
    <div class="calendar-modal__days js-calendar-days"></div>
    <label class="calendar-modal__time">
      <span>時刻</span>
      <input class="calendar-modal__time-input js-calendar-time" type="time">
    </label>
    <div class="calendar-modal__footer">
      <button class="calendar-modal__clear js-calendar-clear" type="button">期限なし</button>
    </div>
  </form>
</div>
@endsection

@section('scripts')
<script>
  const sortableTodos = document.getElementById('sortable-todos');
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
  let draggingRow = null;

  const submitForm = (form) => {
    if (typeof form.requestSubmit === 'function') {
      form.requestSubmit();
      return;
    }

    form.submit();
  };

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

  const calendarModal = document.getElementById('deadline-calendar');
  const calendarForm = calendarModal.querySelector('.calendar-modal__content');
  const calendarTitle = calendarModal.querySelector('.js-calendar-title');
  const calendarDays = calendarModal.querySelector('.js-calendar-days');
  const calendarPrev = calendarModal.querySelector('.js-calendar-prev');
  const calendarNext = calendarModal.querySelector('.js-calendar-next');
  const calendarClear = calendarModal.querySelector('.js-calendar-clear');
  const calendarTime = calendarModal.querySelector('.js-calendar-time');
  let activeDeadlineDateInput = null;
  let activeDeadlineTimeInput = null;
  let activeDeadlineLabel = null;
  let activeDeadlineForm = null;
  let visibleCalendarDate = new Date();
  let selectedDeadlineDate = '';

  const formatDateValue = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
  };

  const formatDeadlineLabel = (dateValue, timeValue) => {
    if (!dateValue) {
      return '未設定';
    }

    const [year, month, day] = dateValue.split('-');
    return `${year}/${month}/${day}${timeValue ? ` ${timeValue}` : ''}`;
  };

  const applyDeadlineValue = () => {
    if (!activeDeadlineDateInput || !activeDeadlineTimeInput || !activeDeadlineLabel) {
      return;
    }

    activeDeadlineDateInput.value = selectedDeadlineDate;
    activeDeadlineTimeInput.value = calendarTime.value;
    activeDeadlineLabel.textContent = formatDeadlineLabel(selectedDeadlineDate, calendarTime.value);
  };

  const submitCalendarForm = () => {
    if (typeof calendarForm.requestSubmit === 'function') {
      calendarForm.requestSubmit();
      return;
    }

    calendarForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
  };

  const submitActiveUpdateForm = () => {
    if (!activeDeadlineForm || !activeDeadlineForm.classList.contains('update-form')) {
      return;
    }

    submitForm(activeDeadlineForm);
  };

  const parseDateValue = (dateValue) => {
    if (!dateValue) {
      return new Date();
    }

    const [year, month, day] = dateValue.split('-').map(Number);
    return new Date(year, month - 1, day);
  };

  const renderCalendar = () => {
    const year = visibleCalendarDate.getFullYear();
    const month = visibleCalendarDate.getMonth();
    const selectedValue = selectedDeadlineDate || activeDeadlineDateInput?.value || '';
    const todayValue = formatDateValue(new Date());
    const firstDate = new Date(year, month, 1);
    const lastDate = new Date(year, month + 1, 0);

    calendarTitle.textContent = `${year}年 ${month + 1}月`;
    calendarDays.innerHTML = '';

    for (let i = 0; i < firstDate.getDay(); i++) {
      const blank = document.createElement('span');
      blank.className = 'calendar-modal__blank';
      calendarDays.appendChild(blank);
    }

    for (let day = 1; day <= lastDate.getDate(); day++) {
      const date = new Date(year, month, day);
      const dateValue = formatDateValue(date);
      const button = document.createElement('button');
      button.className = 'calendar-modal__day';
      button.type = 'button';
      button.textContent = day;
      button.dataset.date = dateValue;

      if (dateValue === todayValue) {
        button.classList.add('calendar-modal__day--today');
      }

      if (dateValue === selectedValue) {
        button.classList.add('calendar-modal__day--selected');
      }

      calendarDays.appendChild(button);
    }
  };

  const openCalendar = (button) => {
    const pickerRoot = button.closest('.create-form__deadline, .update-form__item--deadline');
    activeDeadlineDateInput = pickerRoot.querySelector('.js-deadline-date');
    activeDeadlineTimeInput = pickerRoot.querySelector('.js-deadline-time');
    activeDeadlineLabel = pickerRoot.querySelector('.js-deadline-label');
    activeDeadlineForm = pickerRoot.closest('form');
    selectedDeadlineDate = activeDeadlineDateInput.value || formatDateValue(new Date());
    calendarTime.value = activeDeadlineTimeInput.value;
    visibleCalendarDate = parseDateValue(selectedDeadlineDate);

    calendarModal.classList.add('calendar-modal--open');
    calendarModal.setAttribute('aria-hidden', 'false');
    renderCalendar();
  };

  const closeCalendar = () => {
    calendarModal.classList.remove('calendar-modal--open');
    calendarModal.setAttribute('aria-hidden', 'true');
  };

  document.querySelectorAll('.js-deadline-open').forEach((button) => {
    button.addEventListener('click', () => openCalendar(button));
  });

  calendarDays.addEventListener('click', (event) => {
    const dayButton = event.target.closest('.calendar-modal__day');

    if (!dayButton || !activeDeadlineDateInput || !activeDeadlineTimeInput || !activeDeadlineLabel) {
      return;
    }

    selectedDeadlineDate = dayButton.dataset.date;
    applyDeadlineValue();
    renderCalendar();
  });

  calendarDays.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') {
      return;
    }

    const dayButton = event.target.closest('.calendar-modal__day');

    if (!dayButton) {
      return;
    }

    event.preventDefault();
    selectedDeadlineDate = dayButton.dataset.date;
    submitCalendarForm();
  });

  calendarTime.addEventListener('input', () => {
    applyDeadlineValue();
  });

  calendarTime.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') {
      return;
    }

    event.preventDefault();
    submitCalendarForm();
  });

  calendarPrev.addEventListener('click', () => {
    visibleCalendarDate = new Date(visibleCalendarDate.getFullYear(), visibleCalendarDate.getMonth() - 1, 1);
    renderCalendar();
  });

  calendarNext.addEventListener('click', () => {
    visibleCalendarDate = new Date(visibleCalendarDate.getFullYear(), visibleCalendarDate.getMonth() + 1, 1);
    renderCalendar();
  });

  calendarClear.addEventListener('click', () => {
    if (activeDeadlineDateInput && activeDeadlineTimeInput && activeDeadlineLabel) {
      activeDeadlineDateInput.value = '';
      activeDeadlineTimeInput.value = '';
      activeDeadlineLabel.textContent = '未設定';
    }

    closeCalendar();
    submitActiveUpdateForm();
  });

  calendarForm.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!activeDeadlineDateInput || !activeDeadlineTimeInput || !activeDeadlineLabel) {
      return;
    }

    applyDeadlineValue();
    closeCalendar();

    if (activeDeadlineForm.classList.contains('create-form')) {
      submitForm(activeDeadlineForm);
    } else {
      submitActiveUpdateForm();
    }
  });

  document.querySelectorAll('.js-submit-on-enter').forEach((input) => {
    input.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter') {
        return;
      }

      event.preventDefault();
      submitForm(input.closest('form'));
    });
  });

  document.querySelectorAll('.js-calendar-close').forEach((button) => {
    button.addEventListener('click', closeCalendar);
  });
</script>
@endsection
