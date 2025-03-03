<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'break_times' => ['nullable', 'array'],
            'break_times.*.start' => ['nullable', 'date_format:H:i', 'before_or_equal:end_time'],
            'break_times.*.end' => ['nullable', 'date_format:H:i', 'after_or_equal:break_times.*.start', 'before_or_equal:end_time'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'end_time.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_times.*.start.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_times.*.end.after_or_equal' => '休憩終了時間が休憩開始時間より前になっています。',
            'break_times.*.end.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
        ];
    }
}
