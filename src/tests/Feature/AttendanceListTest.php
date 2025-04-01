<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /**
     * 自分の勤怠情報がすべて表示されている
     */
    public function test_user_can_see_all_their_attendance()
    {
        Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/attendance/list');
        $this->assertCount(3, $response->viewData('attendances'));
    }

    /**
     * 勤怠一覧に現在の月が表示される
     */
    public function test_current_month_is_displayed_on_attendance_list()
    {
        $response = $this->get('/attendance/list');
        $response->assertSee(Carbon::now()->format('Y/m'));
    }


    /**
     * 前月の勤怠情報が表示される
     */
    public function test_can_view_previous_month_data()
    {
        $lastMonth = Carbon::now()->subMonth();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $lastMonth->copy()->startOfMonth(),
        ]);

        $response = $this->get('/attendance/list?month=' . $lastMonth->format('Y-m'));
        $response->assertSee($lastMonth->format('Y/m'));
    }

    /**
     * 翌月の勤怠情報が表示される
     */
    public function test_can_view_next_month_data()
    {
        $nextMonth = Carbon::now()->addMonth();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $nextMonth->copy()->startOfMonth(),
        ]);

        $response = $this->get('/attendance/list?month=' . $nextMonth->format('Y-m'));
        $response->assertSee($nextMonth->format('Y/m'));
    }

    /**
     * 詳細ボタンから詳細画面に遷移する
     */
    public function test_detail_button_redirects_to_detail_page()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));
    }

    /**
     * 出勤中のユーザーに「退勤」ボタンが表示される
     */
    public function test_clock_out_button_is_visible_when_working()
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
            'status' => 1,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤');
    }

    /**
     * 出勤中のユーザーが退勤ボタンを押すと退勤済になる
     */
    public function test_user_can_clock_out_successfully()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
            'status' => 1,
        ]);

        $response = $this->post('/attendance/end');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 3,
        ]);
    }

    /**
     * 管理画面で退勤時刻が表示される
     */
    public function test_admin_can_see_clock_out_time()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
            'end_time' => now(),
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');
        $response->assertSee($attendance->end_time->format('H:i'));
    }
}
