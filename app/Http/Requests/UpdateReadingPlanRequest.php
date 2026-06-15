<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_date.required'       => '期日を入力してください。',
            'target_date.date'           => '期日は正しい日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は今日以降の日付を入力してください。',
        ];
    }
}
