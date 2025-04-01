<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤時間が退勤時間より後の場合、バリデーションエラーが表示される
     */
    public function test_start_time_after_end_time_shows_error()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'reason' => 'テスト理由',
        ]);

        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 休憩開始時間が退勤時間より後の場合、バリデーションエラーが表示される
     */
    public function test_break_start_after_end_time_shows_error()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '19:00', 'end' => '20:00'],
            ],
            'reason' => 'テスト理由',
        ]);

        $response->assertSessionHasErrors([
            'break_times.0.start' => '休憩時間が勤務時間外です',
        ]);
    }

    /**
     * 休憩終了時間が退勤時間より後の場合、バリデーションエラーが表示される
     */
    public function test_break_end_after_end_time_shows_error()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '12:00', 'end' => '19:00'],
            ],
            'reason' => 'テスト理由',
        ]);

        $response->assertSessionHasErrors([
            'break_times.0.end' => '休憩時間が勤務時間外です',
        ]);
    }

    /**
     * 備考欄が未入力の場合、バリデーションエラーが表示される
     */
    public function test_empty_reason_shows_error()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'reason' => '',
        ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    /**
     * 正常なデータで修正申請が通る
     */
    public function test_valid_request_submits_successfully()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'reason' => '体調不良による遅刻',
        ]);

        $response->assertRedirect(route('attendance.show', $attendance->id));
        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => '体調不良による遅刻',
            'status' => 0,
        ]);
    }

    /**
     * 11-6: 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_user_can_see_all_pending_requests()
    {
        $user = User::factory()->create();
        $attendance1 = Attendance::factory()->create(['user_id' => $user->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id]);

        $request1 = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'reason' => '体調不良による遅刻1',
            'status' => 0,
        ]);

        $request2 = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance2->id,
            'reason' => '体調不良による遅刻2',
            'status' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('体調不良による遅刻1');
        $response->assertSee('体調不良による遅刻2');
    }

    /**
     * 11-7: 「承認済み」に管理者が承認した修正申請が全て表示されている
     */
    public function test_user_can_see_all_approved_requests()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);
        $attendance1 = Attendance::factory()->create(['user_id' => $user->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id]);

        $request1 = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'reason' => '勤務時間修正1',
            'status' => 1,
        ]);

        $request2 = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance2->id,
            'reason' => '勤務時間修正2',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('stamp_correction_request.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('勤務時間修正1');
        $response->assertSee('勤務時間修正2');
    }

    /**
     * 11-8: 各申請の「詳細」を押下すると申請詳細画面に遷移する
     */
    public function test_user_can_view_request_detail()
    {
        $user = User::factory()->create([
            'name' => '中津川 健一',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-04-01',
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => '修正申請の詳細確認',
            'status' => 0,
        ]);

        $this->actingAs($user);

        $listResponse = $this->get(route('stamp_correction_request.list'));
        $listResponse->assertStatus(200);

        $listResponse->assertSee('詳細', false);

        $attendanceDetailUrl = route('attendance.show', ['id' => $attendance->id]);
        $listResponse->assertSee('action="' . $attendanceDetailUrl . '"', false);

        $detailResponse = $this->get($attendanceDetailUrl);
        $detailResponse->assertStatus(200);

        $detailResponse->assertSee('2025年');
        $detailResponse->assertSee('4月1日');
        $detailResponse->assertSee('中津川 健一');
    }
}
