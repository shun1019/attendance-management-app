<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminStaffInfoTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users = [];
    protected $attendances = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $this->users[] = User::factory()->create([
                'name' => "テストユーザー{$i}",
                'email' => "test{$i}@example.com",
                'password' => bcrypt('password'),
                'role' => 0,
            ]);
        }

        $currentDate = Carbon::now();
        $this->createAttendanceData($this->users[0], $currentDate);

        $prevMonth = Carbon::now()->subMonth();
        $this->createAttendanceData($this->users[0], $prevMonth);

        $nextMonth = Carbon::now()->addMonth();
        $this->createAttendanceData($this->users[0], $nextMonth);
    }

    private function createAttendanceData($user, $date)
    {
        $targetDate = Carbon::parse($date)->setDay(15)->format('Y-m-d');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $targetDate,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'reason' => '通常勤務',
        ]);

        $this->attendances[] = $attendance;

        return $attendance;
    }

    /**
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_view_all_users_info()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.staff.index'));

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /**
     * ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_view_user_attendance_list()
    {
        $this->actingAs($this->admin);

        $user = $this->users[0];
        $response = $this->get(route('admin.staff.show', ['id' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);

        $currentMonthFormat = Carbon::now()->format('Y/m');
        $response->assertSee($currentMonthFormat);

        $workDate = Carbon::parse($this->attendances[0]->work_date)->format('m/d');
        $response->assertSee($workDate);
    }

    /**
     *「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_view_previous_month()
    {
        $this->actingAs($this->admin);

        $user = $this->users[0];
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $response = $this->get(route('admin.staff.show', [
            'id' => $user->id,
            'month' => $prevMonth
        ]));

        $response->assertStatus(200);

        $prevMonthFormat = Carbon::now()->subMonth()->format('Y/m');
        $response->assertSee($prevMonthFormat);

        $workDate = Carbon::parse($this->attendances[1]->work_date)->format('m/d');
        $response->assertSee($workDate);
    }

    /**
     *「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_admin_can_view_next_month()
    {
        $this->actingAs($this->admin);

        $user = $this->users[0];
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->get(route('admin.staff.show', [
            'id' => $user->id,
            'month' => $nextMonth
        ]));

        $response->assertStatus(200);

        $nextMonthFormat = Carbon::now()->addMonth()->format('Y/m');
        $response->assertSee($nextMonthFormat);

        $workDate = Carbon::parse($this->attendances[2]->work_date)->format('m/d');
        $response->assertSee($workDate);
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_can_navigate_to_attendance_detail()
    {
        $this->actingAs($this->admin);

        $user = $this->users[0];
        $response = $this->get(route('admin.staff.show', ['id' => $user->id]));

        $response->assertStatus(200);

        $attendance = $this->attendances[0];
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);

        $response->assertSee($detailUrl, false);

        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        $yearPart = Carbon::parse($attendance->work_date)->format('Y年');
        $monthDayPart = Carbon::parse($attendance->work_date)->format('n月j日');

        $detailResponse->assertSee($yearPart);
        $detailResponse->assertSee($monthDayPart);
        $detailResponse->assertSee($attendance->start_time->format('H:i'));
        $detailResponse->assertSee($attendance->end_time->format('H:i'));
    }
}
