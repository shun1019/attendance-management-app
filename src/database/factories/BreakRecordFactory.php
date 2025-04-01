<?php

namespace Database\Factories;

use App\Models\BreakRecord;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakRecordFactory extends Factory
{
    protected $model = BreakRecord::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ];
    }
}
