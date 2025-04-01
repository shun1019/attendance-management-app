<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * 当日の全ユーザーの勤怠情報が表示される
     */
    public function test_all_users_attendance_are_displayed_for_today()
    {
        $date = Carbon::today()->toDateString();

        $users = User::factory()->count(3)->create(['role' => 0]);

        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'start_time' => '09:00',
                'end_time' => '18:00',
            ]);
        }

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 初期表示で今日の日付が表示される
     */
    public function test_today_date_is_displayed_on_initial_view()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y年m月d日'));
    }

    /**
     * 「前日」ボタンで前日の勤怠情報が表示される
     */
    public function test_previous_day_attendance_is_displayed()
    {
        $date = Carbon::yesterday()->toDateString();
        $user = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
            'start_time' => '10:00',
            'end_time' => '19:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee(Carbon::yesterday()->format('Y年m月d日'));
    }

    /**
     * 「翌日」ボタンで翌日の勤怠情報が表示される
     */
    public function test_next_day_attendance_is_displayed()
    {
        $date = Carbon::tomorrow()->toDateString();
        $user = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200);
        $response->assertSee('08:00');
        $response->assertSee('17:00');
        $response->assertSee(Carbon::tomorrow()->format('Y年m月d日'));
    }
}
