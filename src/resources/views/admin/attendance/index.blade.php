@extends('layouts.admin.app')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h1>{{ \Carbon\Carbon::parse($selectedDate)->format('Y年m月d日') }} の勤怠</h1>

    {{-- 日付選択 --}}
    <div class="date-selector">
        <form action="{{ route('admin.attendance.index') }}" method="GET">
            <button type="submit" name="date" value="{{ \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d') }}">← 前日</button>
            <span>{{ \Carbon\Carbon::parse($selectedDate)->format('Y/m/d') }}</span>
            <button type="submit" name="date" value="{{ \Carbon\Carbon::parse($selectedDate)->addDay()->format('Y-m-d') }}">翌日 →</button>
        </form>
    </div>

    {{-- 勤怠一覧表 --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ optional($attendance->start_time)->format('H:i') ?: '' }}</td>
                <td>{{ optional($attendance->end_time)->format('H:i') ?: '' }}</td>
                <td>
                    @if ($attendance->getTotalBreakTime() > 0)
                    {{ gmdate("H:i", $attendance->getTotalBreakTime()) }}
                    @endif
                </td>
                <td>
                    @if ($attendance->start_time && $attendance->end_time)
                    @php
                    $totalTime = strtotime($attendance->end_time) - strtotime($attendance->start_time) - $attendance->getTotalBreakTime();
                    @endphp
                    {{ $totalTime > 0 ? gmdate("H:i", $totalTime) : '' }}
                    @endif
                </td>
                <td>
                    <form action="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" method="GET">
                        <button type="submit" class="details-btn">詳細</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection