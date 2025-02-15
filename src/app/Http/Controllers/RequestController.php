<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * ユーザーの修正申請一覧を表示
     */
    public function index()
    {
        return view('requests.index');
    }
}
