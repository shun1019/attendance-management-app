<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
}
