<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務中のユーザーに退勤ボタンが表示される
     */
    public function test_working_user_can_see_leave_button_and_leave()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
            'status' => 1,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        $response = $this->post('/attendance/end');
        $response->assertRedirect('/attendance');

        $attendance->refresh();
        $this->assertNotNull($attendance->end_time);
        $this->assertEquals(3, $attendance->status);
    }

    /**
     * 退勤時刻が管理画面に表示される
     */
    public function test_admin_can_see_leave_time_on_admin_screen()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/start');
        $this->post('/attendance/end');

        $attendance = Attendance::first();

        $admin = User::factory()->create(['role' => 1]);
        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');
        $response->assertSee($attendance->end_time->format('H:i'));
    }
}
