<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    /**
     * 全スタッフの一覧を表示（管理者）
     */
    public function index()
    {
        return view('admin.staff.index');
    }
}
