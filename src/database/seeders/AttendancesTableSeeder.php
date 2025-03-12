<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('attendances')->insert([
            [
                'user_id' => 2,
                'work_date' => Carbon::parse('2025-03-10')->toDateString(),
                'start_time' => Carbon::parse('2025-03-10 09:00:00'),
                'end_time' => Carbon::parse('2025-03-10 18:00:00'),
                'status' => 3,
                'reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('break_records')->insert([
            [
                'attendance_id' => 1,
                'break_start' => Carbon::parse('2025-03-10 12:00:00'),
                'break_end' => Carbon::parse('2025-03-10 12:30:00'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
