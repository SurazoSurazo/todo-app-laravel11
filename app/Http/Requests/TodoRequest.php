<?php

namespace App\Http\Requests;

use App\Models\Todo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TodoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->isMethod('post')) {
            $this->merge([
                'status' => $this->input('status', Todo::STATUS_NOT_STARTED),
            ]);
        }

        if ($this->has('deadline_date')) {
            $deadlineDate = $this->input('deadline_date');
            $deadlineTime = $this->input('deadline_time');

            $this->merge([
                'deadline_at' => $deadlineDate
                    ? $deadlineDate . ($deadlineTime ? ' ' . $deadlineTime : '')
                    : null,
            ]);
        }
    }

    public function rules()
    {
        return [
            'content' => ['required', 'string', 'max:20'],
            'status' => [$this->isMethod('post') ? 'required' : 'sometimes', Rule::in(Todo::STATUSES)],
            'priority' => ['nullable', Rule::in(Todo::PRIORITIES)],
            'category_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
            'deadline_date' => ['nullable', 'date'],
            'deadline_time' => ['nullable', 'date_format:H:i'],
            'deadline_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $deadlineDate = $this->input('deadline_date');

            if (!$deadlineDate || $validator->errors()->has('deadline_date')) {
                return;
            }

            $today = Carbon::today();
            $deadlineDay = Carbon::parse($deadlineDate)->startOfDay();

            if ($deadlineDay->lt($today)) {
                $validator->errors()->add('deadline_date', '過去の日付は期限に設定できません');
                return;
            }

            $deadlineTime = $this->input('deadline_time');

            if (
                $deadlineDay->isSameDay($today)
                && $deadlineTime
                && !$validator->errors()->has('deadline_time')
                && $deadlineTime < Carbon::now()->format('H:i')
            ) {
                $validator->errors()->add('deadline_time', '過去の時刻は期限に設定できません');
            }
        });
    }

    public function messages()
    {
        return [
            'content.required' => 'Todoを入力してください',
            'content.string' => 'Todoを文字列で入力してください',
            'content.max' => 'Todoを20文字以下で入力してください',
            'status.in' => '状態を正しく選択してください',
            'priority.in' => '優先度を正しく選択してください',
            'category_id.required' => 'カテゴリを入力してください',
            'category_id.exists' => 'カテゴリを正しく選択してください',
            'deadline_date.date' => '期限日を正しい日付で入力してください',
            'deadline_time.date_format' => '期限時刻を正しい時刻で入力してください',
            'deadline_at.date' => '期限を正しい日時で入力してください',
        ];
    }
}
