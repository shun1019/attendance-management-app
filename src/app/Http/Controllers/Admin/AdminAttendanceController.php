<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    /**
     * 全ユーザーの勤怠情報一覧を表示（管理者）
     */
    public function index()
    {
        return view('admin.attendance.index');
    }

    /**
     * 特定ユーザーの勤怠詳細を表示（管理者）
     */
    public function show($id)
    {
        return view('admin.attendance.show', compact('id'));
    }

    /**
     * スタッフごとの勤怠履歴を表示（管理者）
     */
    public function listByStaff($id)
    {
        return view('admin.attendance.attendance', compact('id'));
    }
}
