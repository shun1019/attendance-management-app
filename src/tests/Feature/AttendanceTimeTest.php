<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;

class AttendanceTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_attendance_screen_displays_current_date_and_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow(Carbon::create(2025, 3, 25, 14, 30));

        $response = $this->get('/attendance');

        $expectedDate = Carbon::now()->isoFormat('YYYY年MM月DD日 (ddd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
