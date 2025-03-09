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
            'start_time' => ['nullable', 'date_format:H:i', 'before_or_equal:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'break_times' => ['nullable', 'array'],
            'break_times.*.start' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time', 'before_or_equal:end_time'],
            'break_times.*.end' => ['nullable', 'date_format:H:i', 'after_or_equal:break_times.*.start', 'before_or_equal:end_time'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_time.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_times.*.start.after_or_equal' => '休憩時間が勤務時間外です',
            'break_times.*.start.before_or_equal' => '休憩時間が勤務時間外です',
            'break_times.*.end.after_or_equal' => '休憩時間が勤務時間外です',
            'break_times.*.end.before_or_equal' => '休憩時間が勤務時間外です',
            'reason.required' => '備考を記入してください',
        ];
    }
}
