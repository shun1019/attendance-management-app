<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    /**
     * 本日の勤怠情報を表示
     */
    public function index()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->with('breakRecords')
            ->first();

        return view('attendance.attendance', compact('attendance'));
    }

    /**
     * 勤怠履歴を月単位で表示
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        $yearMonth = $request->input('month', now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [
                Carbon::parse($yearMonth)->startOfMonth()->toDateString(),
                Carbon::parse($yearMonth)->endOfMonth()->toDateString()
            ])
            ->orderBy('work_date')
            ->with('breakRecords')
            ->get();

        return view('attendance.index', compact('attendances', 'yearMonth'));
    }

    /**
     * 出勤打刻
     */
    public function start(Request $request)
    {
        if (Attendance::where('user_id', Auth::id())->where('work_date', now()->toDateString())->exists()) {
            return redirect()->route('attendance.index');
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'work_date' => now()->toDateString(),
            'start_time' => now(),
            'status' => 1,
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 退勤打刻
     */
    public function end(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->first();

        if (!$attendance || $attendance->status !== 1) {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'end_time' => now(),
            'status' => 3,
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩開始打刻
     */
    public function breakStart(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->first();

        if (!$attendance || $attendance->status !== 1) {
            return redirect()->route('attendance.index');
        }

        BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        $attendance->update(['status' => 2]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩終了打刻
     */
    public function breakEnd(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->first();

        if (!$attendance || $attendance->status !== 2) {
            return redirect()->route('attendance.index');
        }

        $breakRecord = BreakRecord::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if (!$breakRecord) {
            return redirect()->route('attendance.index');
        }

        $breakRecord->update(['break_end' => now()]);

        $attendance->update(['status' => 1]);

        return redirect()->route('attendance.index');
    }

    /**
     * 勤怠詳細を表示（管理者と一般ユーザーで画面分岐）
     */
    public function show($id, Request $request)
    {
        $attendance = Attendance::with('user', 'breakRecords')->findOrFail($id);
        $user = Auth::user();

        if ($user->role == 1) {
            $requestId = $request->query('request_id');
            if ($requestId) {
                $attendanceRequest = AttendanceRequest::findOrFail($requestId);
                return view('admin.stamp_correction_request.approve', [
                    'request' => $attendanceRequest
                ]);
            }

            return view('admin.attendance.show', compact('attendance'));
        }

        if ($attendance->user_id != $user->id) {
            abort(403, '閲覧権限がありません');
        }

        $pendingRequest = AttendanceRequest::where('attendance_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 0)
            ->first();

        return view('attendance.show', compact('attendance', 'pendingRequest'));
    }

    /**
     * 勤怠修正申請の登録
     */
    public function requestUpdate(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $id,
            'new_start_time' => $request->start_time,
            'new_end_time' => $request->end_time,
            'new_break_times' => json_encode($request->break_times ?? []),
            'reason' => $request->reason ?? '',
            'status' => 0,
        ]);

        return redirect()->route('attendance.show', $id);
    }
}
