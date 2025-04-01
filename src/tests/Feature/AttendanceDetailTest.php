<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面に氏名・日付・出退勤・休憩時間が正しく表示される
     */
    public function test_attendance_detail_page_displays_correct_information()
    {
        $user = User::factory()->create(['name' => '山田 太郎']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::parse('2024-03-30'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        BreakRecord::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $response->assertSee('山田 太郎');
        $response->assertSee('2024年');
        $response->assertSee('3月30日');

        $html = $response->getContent();

        $this->assertStringContainsString('value="09:00"', $html);
        $this->assertStringContainsString('value="18:00"', $html);
        $this->assertStringContainsString('value="12:00"', $html);
        $this->assertStringContainsString('value="13:00"', $html);
    }
}
