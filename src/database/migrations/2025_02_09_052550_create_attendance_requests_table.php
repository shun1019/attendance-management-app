<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->time('new_start_time')->nullable(); // 変更後の出勤時間
            $table->time('new_end_time')->nullable(); // 変更後の退勤時間
            $table->json('new_break_times')->nullable(); // 変更後の休憩時間（JSON形式）
            $table->text('reason'); // 申請理由
            $table->tinyInteger('status')->default(0); // 0: 承認待ち, 1: 承認済み, 2: 却下
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}