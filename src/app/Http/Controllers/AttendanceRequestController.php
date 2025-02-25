<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 承認待ちの申請一覧を取得
        $pendingRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 0)
            ->with('attendance', 'user')
            ->get();

        // 承認済みの申請一覧を取得
        $approvedRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 1)
            ->with('attendance', 'user')
            ->get();

        return view('stamp_correction_request.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests
        ]);
    }
}
