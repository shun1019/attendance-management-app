<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外のステータスが表示される
     */
    public function test_status_is_out_of_work_when_no_attendance_exists()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中のステータスが表示される
     */
    public function test_status_is_working_when_status_is_1()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(2),
            'status' => 1,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中のステータスが表示される
     */
    public function test_status_is_on_break_when_status_is_2()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
            'status' => 2,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済のステータスが表示される
     */
    public function test_status_is_left_work_when_status_is_3()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(8),
            'end_time' => now(),
            'status' => 3,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}
