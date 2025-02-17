<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $tab = $request->query('tab', 'pending');

        // 承認待ちの申請
        $pendingRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 0)
            ->with('attendance', 'user')
            ->latest()
            ->get();

        // 承認済みの申請
        $approvedRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 1)
            ->with('attendance', 'user')
            ->latest()
            ->get();

        return view('stamp_correction_request.index', compact('pendingRequests', 'approvedRequests', 'tab'));
    }
}
