<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧を表示
     */
    public function index()
    {
        $staffs = User::where('role', 0)->get();
        return view('admin.staff.index', compact('staffs'));
    }

    /**
     * スタッフごとの月次勤怠一覧を表示
     */
    public function show($id, Request $request)
    {
        $user = User::findOrFail($id);
        $yearMonth = $request->input('month', now()->format('Y-m'));

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
     * スタッフの勤怠データをCSVでエクスポート
     */
    public function exportCsv($id, Request $request)
    {
        $user = User::findOrFail($id);
        $yearMonth = $request->input('month', now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [
                Carbon::parse($yearMonth)->startOfMonth()->toDateString(),
                Carbon::parse($yearMonth)->endOfMonth()->toDateString()
            ])
            ->orderBy('work_date')
            ->get();

        $csvHeader = ['日付', '出勤時間', '退勤時間', '休憩時間', '合計勤務時間'];

        $response = new StreamedResponse(function () use ($attendances, $csvHeader) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvHeader);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    Carbon::parse($attendance->work_date)->format('Y/m/d'),
                    optional($attendance->start_time)->format('H:i') ?: '',
                    optional($attendance->end_time)->format('H:i') ?: '',
                    $attendance->getTotalBreakTime() > 0 ? gmdate("H:i", $attendance->getTotalBreakTime()) : '',
                    ($attendance->start_time && $attendance->end_time)
                        ? gmdate("H:i", strtotime($attendance->end_time) - strtotime($attendance->start_time) - $attendance->getTotalBreakTime())
                        : ''
                ]);
            }

            fclose($handle);
        });

        $fileName = $user->name . '_' . Carbon::parse($yearMonth)->format('Y-m') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
