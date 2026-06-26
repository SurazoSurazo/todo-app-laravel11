<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TodoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => ['required', 'string', 'max:20'],
            'category_id' => [$this->isMethod('post') ? 'required' : 'sometimes'],
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
            'deadline_at.date' => '期限を正しい日時で入力してください',
        ];
    }
}
