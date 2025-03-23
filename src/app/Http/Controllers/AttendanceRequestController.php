<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    /**
     * ユーザーの修正申請一覧（承認待ち・承認済み）
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'pending');

        if ($user->role == 1) {
            $adminController = new \App\Http\Controllers\Admin\AdminRequestController();
            return $adminController->index($request);
        }

        $userId = $user->id;

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
            'approvedRequests' => $approvedRequests,
            'tab' => $tab
        ]);
    }

    /**
     * 申請の詳細を表示
     */
    public function show($id)
    {
        if (Auth::user()->role == 1) {
            $adminController = new AdminRequestController();
            return $adminController->show($id);
        }

        $request = AttendanceRequest::with(['attendance', 'user'])->findOrFail($id);

        if ($request->user_id != Auth::id()) {
            abort(403, '閲覧権限がありません');
        }

        return redirect()->route('attendance.show', ['id' => $request->attendance_id]);
    }
}
