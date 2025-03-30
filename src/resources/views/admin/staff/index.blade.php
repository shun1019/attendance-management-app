@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('content')
<div class="staff-list-container">
    <h1>スタッフ一覧</h1>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffs as $staff)
            <tr>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->email }}</td>
                <td>
                    <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}" class="details-btn">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection