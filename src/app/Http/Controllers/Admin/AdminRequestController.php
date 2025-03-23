<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;

class AdminRequestController extends Controller
{
    /**
     * 修正申請の一覧表示（承認待ち・承認済み）
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $pendingRequests = AttendanceRequest::where('status', 0)
            ->with(['user', 'attendance'])
            ->latest()
            ->get();

        $approvedRequests = AttendanceRequest::where('status', 1)
            ->with(['user', 'attendance'])
            ->latest()
            ->get();

        return view('admin.stamp_correction_request.index', compact('pendingRequests', 'approvedRequests', 'tab'));
    }

    /**
     * 申請の詳細を表示
     */
    public function show($id)
    {
        $request = AttendanceRequest::with(['attendance', 'user'])->findOrFail($id);

        return redirect()->route('stamp_correction_request.approve.form', ['attendance_correct_request' => $id]);
    }

    /**
     * 申請承認フォームを表示
     */
    public function showApproveForm($id)
    {
        $request = AttendanceRequest::with(['attendance', 'user'])->findOrFail($id);
        return view('admin.stamp_correction_request.approve', compact('request'));
    }

    /**
     * 修正申請の承認処理
     */
    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $request = AttendanceRequest::findOrFail($id);
            $attendance = $request->attendance;

            $attendance->start_time = $request->new_start_time ?? $attendance->start_time;
            $attendance->end_time = $request->new_end_time ?? $attendance->end_time;
            $attendance->save();

            if (!empty($request->new_break_times)) {
                $breakTimes = json_decode($request->new_break_times, true);

                $attendance->breakRecords()->delete();
                foreach ($breakTimes as $break) {
                    $attendance->breakRecords()->create([
                        'break_start' => $break['start'],
                        'break_end' => $break['end']
                    ]);
                }
            }

            $request->status = 1;
            $request->save();

            DB::commit();

            return redirect()->route('stamp_correction_request.list', ['tab' => 'approved'])
                ->with('success', '申請を承認しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', '承認処理中にエラーが発生しました: ' . $e->getMessage());
        }
    }
}
