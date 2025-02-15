<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 出勤登録画面（出勤・退勤ボタンを表示）
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
     * 勤怠一覧を表示（一般ユーザー）
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
            ->with('breakRecords') // 休憩データを取得
            ->get();

        return view('attendance.index', compact('attendances', 'yearMonth'));
    }

    /**
     * 出勤処理
     */
    public function start(Request $request)
    {
        if (Attendance::where('user_id', Auth::id())->where('work_date', now()->toDateString())->exists()) {
            return redirect()->route('attendance.index')->with('error', 'すでに出勤しています。');
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'work_date' => now()->toDateString(),
            'start_time' => now(),
            'status' => 1, // 出勤中
        ]);

        return redirect()->route('attendance.index')->with('success', '出勤しました！');
    }

    /**
     * 退勤処理
     */
    public function end(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->first();

        if (!$attendance || $attendance->status !== 1) {
            return redirect()->route('attendance.index')->with('error', '出勤していません。');
        }

        $attendance->update([
            'end_time' => now(),
            'status' => 3, // 退勤済
        ]);

        return redirect()->route('attendance.index')->with('success', '退勤しました！');
    }

    /**
     * 休憩開始
     */
    public function breakStart(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->first();

        if (!$attendance || $attendance->status !== 1) {
            return redirect()->route('attendance.index')->with('error', '出勤していません。');
        }

        // 休憩開始のレコードを作成
        BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        // 出勤状態を「休憩中」に変更
        $attendance->update(['status' => 2]);

        return redirect()->route('attendance.index')->with('success', '休憩を開始しました！');
    }

    /**
     * 休憩終了
     */
    public function breakEnd(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->first();

        if (!$attendance || $attendance->status !== 2) {
            return redirect()->route('attendance.index')->with('error', '休憩中ではありません。');
        }

        // 最新の休憩開始レコードを取得
        $breakRecord = BreakRecord::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if (!$breakRecord) {
            return redirect()->route('attendance.index')->with('error', '休憩開始の記録がありません。');
        }

        // 休憩終了時間を更新
        $breakRecord->update(['break_end' => now()]);

        // 出勤状態を「出勤中」に戻す
        $attendance->update(['status' => 1]);

        return redirect()->route('attendance.index')->with('success', '休憩を終了しました！');
    }

    /**
     * 勤怠詳細画面
     */
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakRecords')->findOrFail($id);
        $pendingRequest = AttendanceRequest::where('attendance_id', $id)->where('status', 0)->first(); // 申請中のデータを取得
        return view('attendance.show', compact('attendance', 'pendingRequest'));
    }

    /**
     * 修正申請の処理
     */
    public function requestUpdate(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'break_times' => 'nullable|array',
            'reason' => 'required|string|max:255',
        ]);

        AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $id,
            'new_start_time' => $request->start_time,
            'new_end_time' => $request->end_time,
            'new_break_times' => json_encode($request->break_times ?? []),
            'reason' => $request->reason,
            'status' => 0, // 申請中
        ]);

        return redirect()->route('attendance.show', $id)->with('success', '修正申請を送信しました！');
    }
}
