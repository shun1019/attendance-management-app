<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが表示され、出勤処理ができる
     */
    public function test_user_can_see_start_button_and_start_work()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        $postResponse = $this->post('/attendance/start');
        $postResponse->assertRedirect('/attendance');

        $this->get('/attendance')->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 1,
            'work_date' => Carbon::today()->toDateString(),
        ]);
    }

    /**
     * 出勤は一日一回のみ（退勤済なら出勤ボタンは表示されない）
     */
    public function test_user_cannot_see_start_button_if_already_attended()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(8),
            'end_time' => now(),
            'status' => 3,
        ]);

        $response = $this->get('/attendance');
        $response->assertDontSee('出勤');
    }

    /**
     * 管理画面で出勤時刻が表示される
     */
    public function test_admin_can_see_attendance_start_time()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->setTime(9, 0),
            'status' => 1,
        ]);

        $response = $this->get('/admin/attendance/list?date=' . now()->toDateString());

        $response->assertSee('09:00');
        $response->assertSee($user->name);
    }
}
