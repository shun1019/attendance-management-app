<?php

namespace Database\Factories;

use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRequestFactory extends Factory
{
    protected $model = AttendanceRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'new_start_time' => '09:00',
            'new_end_time' => '18:00',
            'new_break_times' => json_encode([['start' => '12:00', 'end' => '13:00']]),
            'reason' => $this->faker->sentence(),
            'status' => 0,
        ];
    }
}
