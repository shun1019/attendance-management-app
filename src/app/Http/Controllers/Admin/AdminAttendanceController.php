<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Http\Requests\AttendanceUpdateRequest;

class AdminAttendanceController extends Controller
{
    /**
     * 全ユーザーの勤怠情報一覧を表示（管理者）
     */
    public function index(Request $request)
    {
        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        $attendances = Attendance::whereDate('work_date', $selectedDate)
            ->with('user')
            ->get();

        return view('admin.attendance.index', compact('attendances', 'selectedDate'));
    }

    /**
     * 勤怠IDで詳細を表示（管理者）
     */
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakRecords')->findOrFail($id);
        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * スタッフごとの勤怠履歴を表示（管理者）
     */
    public function listByStaff($id)
    {
        $user = User::findOrFail($id);
        $yearMonth = request('month', now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [
                Carbon::parse($yearMonth)->startOfMonth()->toDateString(),
                Carbon::parse($yearMonth)->endOfMonth()->toDateString()
            ])
            ->orderBy('work_date')
            ->get();

        return view('admin.staff.show', compact('user', 'attendances', 'yearMonth'));
    }

    /**
     * スタッフごとの1日単位の勤怠詳細を表示（管理者）
     */
    public function attendanceDetail($id, $attendance_id)
    {
        $user = User::findOrFail($id);
        $attendance = Attendance::with('breakRecords')->findOrFail($attendance_id);

        return view('admin.attendance.show', compact('user', 'attendance'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->start_time = $request->start_time;
        $attendance->end_time = $request->end_time;
        $attendance->reason = $request->reason;
        $attendance->save();

        if ($request->has('break_times')) {
            foreach ($request->break_times as $index => $break) {
                if (!isset($break['start']) || !isset($break['end'])) {
                    continue;
                }

                $breakRecord = $attendance->breakRecords[$index] ?? new BreakRecord(['attendance_id' => $attendance->id]);
                $breakRecord->break_start = $break['start'];
                $breakRecord->break_end = $break['end'];
                $breakRecord->save();
            }
        }

        return redirect()->route('admin.attendance.show', $id);
    }
}