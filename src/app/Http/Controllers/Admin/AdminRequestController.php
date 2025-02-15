<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    /**
     * ユーザーの修正申請一覧を表示（管理者）
     */
    public function index()
    {
        return view('admin.requests.index');
    }

    /**
     * 修正申請の詳細を表示し、承認・却下ボタンを表示
     */
    public function approve($id)
    {
        return view('admin.requests.approve', compact('id'));
    }
}
