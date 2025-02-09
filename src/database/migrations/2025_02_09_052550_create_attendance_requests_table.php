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
            $table->tinyInteger('request_type'); // 1: 出勤, 2: 退勤, 3: 休憩
            $table->json('request_data');
            $table->tinyInteger('status')->default(0); // 0: 承認待ち, 1: 承認済み, 2: 却下
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}
