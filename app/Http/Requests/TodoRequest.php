<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TodoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
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
            'category_id' => [$this->isMethod('post') ? 'required' : 'sometimes'],
            'deadline_date' => ['nullable', 'date'],
            'deadline_time' => ['nullable', 'date_format:H:i'],
            'deadline_at' => ['nullable', 'date'],
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'Todoを入力してください',
            'content.string' => 'Todoを文字列で入力してください',
            'content.max' => 'Todoを20文字以下で入力してください',
            'category_id.required' => 'カテゴリを入力してください',
            'deadline_date.date' => '期限日を正しい日付で入力してください',
            'deadline_time.date_format' => '期限時刻を正しい時刻で入力してください',
            'deadline_at.date' => '期限を正しい日時で入力してください',
        ];
    }
}
