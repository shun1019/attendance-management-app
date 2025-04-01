<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 1]);
        $this->user = User::factory()->create(['role' => 0]);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * 承認待ちの修正申請が表示される
     */
    public function test_admin_can_see_pending_requests()
    {
        $request = AttendanceRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 0, // 承認待ち
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($request->reason);
    }

    /**
     * 承認済みの修正申請が表示される
     */
    public function test_admin_can_see_approved_requests()
    {
        $request = AttendanceRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 1, // 承認済み
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('stamp_correction_request.list') . '?tab=approved');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($request->reason);
    }

    /**
     * 修正申請の詳細画面に遷移し、承認画面にリダイレクトされる
     */
    public function test_admin_can_view_request_detail()
    {
        $request = AttendanceRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'reason' => '体調不良で出勤時間変更',
            'status' => 0, // 承認待ち
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('stamp_correction_request.show', ['id' => $request->id]));

        $response->assertRedirect(route('stamp_correction_request.approve.form', [
            'attendance_correct_request' => $request->id
        ]));
    }

    /**
     * 承認処理で申請が承認され、勤怠情報も更新される
     */
    public function test_admin_can_approve_request_and_update_attendance()
    {
        $request = AttendanceRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'new_start_time' => '09:00',
            'new_end_time' => '18:00',
            'new_break_times' => json_encode([['start' => '12:00', 'end' => '13:00']]),
            'status' => 0,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('stamp_correction_request.approve', ['attendance_correct_request' => $request->id]));

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 1,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
    }
}
