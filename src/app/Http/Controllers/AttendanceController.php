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
    public function index()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->with('breakRecords')
            ->first();

        return view('attendance.attendance', compact('attendance'));
    }

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

    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakRecords')->findOrFail($id);
        $pendingRequest = AttendanceRequest::where('attendance_id', $id)->where('status', 0)->first();

        return view('attendance.show', compact('attendance', 'pendingRequest'));
    }

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
