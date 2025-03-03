<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    /**
     * ユーザーの修正申請一覧（承認待ち・承認済み）
     */
    public function index()
    {
        $userId = Auth::id();

        $pendingRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 0)
            ->with('attendance', 'user')
            ->get();

        $approvedRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 1)
            ->with('attendance', 'user')
            ->get();

        return view('stamp_correction_request.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests
        ]);
    }

    /**
     * 申請の詳細を表示
     */
    public function show($id)
    {
        $request = AttendanceRequest::with(['attendance', 'user'])->findOrFail($id);

        return redirect()->route('attendance.show', ['id' => $request->attendance->id]);
    }
}
