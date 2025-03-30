<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BreakFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_break_start_button_works()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 0
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => date('Y-m-d'),
            'start_time' => '09:00:00',
            'status' => 1
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->post('/attendance/break/start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 2
        ]);
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_break_end_button_works()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'role' => 0
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => date('Y-m-d'),
            'start_time' => '09:00:00',
            'status' => 2
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        $breakEndResponse = $this->post('/attendance/break/end');
        $breakEndResponse->assertStatus(302);
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_multiple_breaks_in_a_day()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー3',
            'email' => 'test3@example.com',
            'password' => bcrypt('password123'),
            'role' => 0
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => date('Y-m-d'),
            'start_time' => '09:00:00',
            'status' => 1
        ]);

        $this->actingAs($user);

        $this->post('/attendance/break/start');
        $this->post('/attendance/break/end');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_break_end_multiple_times()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー4',
            'email' => 'test4@example.com',
            'password' => bcrypt('password123'),
            'role' => 0
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => date('Y-m-d'),
            'start_time' => '09:00:00',
            'status' => 1
        ]);

        $this->actingAs($user);

        $this->post('/attendance/break/start');
        $this->post('/attendance/break/end');
        $this->post('/attendance/break/start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 2
        ]);
    }

    /**
     * 休憩時刻が管理画面で確認できる
     */
    public function test_admin_can_see_break_time()
    {
        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 0
        ]);

        $admin = User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => date('Y-m-d'),
            'start_time' => '09:00:00',
            'status' => 1
        ]);

        $this->actingAs($user);
        $this->post('/attendance/break/start');
        $this->post('/attendance/break/end');

        $this->actingAs($admin);
        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);
        $response->assertSee(date('Y-m-d'));

        $detailResponse = $this->get('/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);
    }
}
