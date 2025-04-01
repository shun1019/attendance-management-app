<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1,
        ]);

        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 0,
        ]);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-03-31',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'reason' => '通常勤務',
        ]);

        $breakRecord = new BreakRecord();
        $breakRecord->attendance_id = $this->attendance->id;
        $breakRecord->break_start = '12:00';
        $breakRecord->break_end = '13:00';
        $breakRecord->save();
    }

    /**
     * 13-1: 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_admin_can_view_attendance_detail()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.staff.show', ['id' => $this->user->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('2025/');
    }

    /**
     * 13-2: 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_start_time_after_end_time_shows_error()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('attendance.update', ['id' => $this->attendance->id]), [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'reason' => '修正テスト',
        ]);

        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 13-3: 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_start_after_end_time_shows_error()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('attendance.update', ['id' => $this->attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '19:00', 'end' => '20:00'],
            ],
            'reason' => '修正テスト',
        ]);

        $response->assertSessionHasErrors([
            'break_times.0.start' => '休憩時間が勤務時間外です'
        ]);
    }

    /**
     * 13-4: 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_end_after_end_time_shows_error()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('attendance.update', ['id' => $this->attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '12:00', 'end' => '19:00'],
            ],
            'reason' => '修正テスト',
        ]);

        $response->assertSessionHasErrors([
            'break_times.0.end' => '休憩時間が勤務時間外です'
        ]);
    }

    /**
     * 13-5: 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_empty_reason_shows_error()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('attendance.update', ['id' => $this->attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'reason' => '',
        ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください'
        ]);
    }

    /**
     * 正常な更新が成功するテスト（追加）
     */
    public function test_valid_update_submits_successfully()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('attendance.update', ['id' => $this->attendance->id]), [
            'start_time' => '10:00',
            'end_time' => '19:00',
            'break_times' => [
                ['start' => '13:00', 'end' => '14:00'],
            ],
            'reason' => '修正：時間調整のため',
        ]);

        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHasNoErrors();

        $this->attendance->refresh();

        $this->assertEquals('10:00', $this->attendance->start_time->format('H:i'));
        $this->assertEquals('19:00', $this->attendance->end_time->format('H:i'));
        $this->assertEquals('修正：時間調整のため', $this->attendance->reason);

        $breakRecord = BreakRecord::where('attendance_id', $this->attendance->id)->first();
        $this->assertEquals('13:00', $breakRecord->break_start->format('H:i'));
        $this->assertEquals('14:00', $breakRecord->break_end->format('H:i'));
    }
}
