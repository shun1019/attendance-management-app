@extends('layouts.app')

@section('title', '勤怠管理')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="status-area">
        <span class="status-label">
            @if (!$attendance)
            勤務外
            @elseif ($attendance->status === 1)
            出勤中
            @elseif ($attendance->status === 2)
            休憩中
            @elseif ($attendance->status === 3)
            退勤済
            @endif
        </span>
    </div>
    <h2>{{ now()->isoFormat('YYYY年MM月DD日 (ddd)') }}</h2>
    <h1>{{ now()->format('H:i') }}</h1>

    @if (!$attendance)
    {{-- 出勤前 --}}
    <form action="{{ route('attendance.start') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">出勤</button>
    </form>
    @elseif ($attendance->status === 1)
    {{-- 出勤中 --}}
    <div class="button-group">
        <form action="{{ route('attendance.end') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">退勤</button>
        </form>
        <form action="{{ route('attendance.break.start') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-warning">休憩入</button>
        </form>
    </div>
    @elseif ($attendance->status === 2)
    {{-- 休憩中 --}}
    <div class="button-group">
        <form action="{{ route('attendance.break.end') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">休憩戻</button>
        </form>
    </div>
    @elseif ($attendance->status === 3)
    {{-- 退勤済 --}}
    <p class="thank-you-message">お疲れ様でした。</p>
    @endif
</div>
@endsection