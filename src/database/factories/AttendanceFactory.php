<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'work_date' => Carbon::today()->toDateString(),
            'start_time' => null,
            'end_time' => null,
            'status' => 0,
            'reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
