<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    /**
     * 勤怠修正申請の一覧を表示
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $tab = $request->query('tab', 'pending');

        $pendingRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 0)
            ->with('attendance', 'user')
            ->latest()
            ->get();

        $approvedRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 1)
            ->with('attendance', 'user')
            ->latest()
            ->get();

        return view('stamp_correction_request.index', compact('pendingRequests', 'approvedRequests', 'tab'));
    }
}
