@extends('layouts.admin.app')

@section('title', $user->name . 'さんの勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h1>{{ $user->name }}さんの勤怠</h1>

    <div class="month-selector">
        <form action="{{ route('admin.staff.show', ['id' => $user->id]) }}" method="GET">
            <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($yearMonth)->subMonth()->format('Y-m') }}">← 前月</button>
            <span class="current-month">{{ \Carbon\Carbon::parse($yearMonth)->format('Y年m月') }}</span>
            <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($yearMonth)->addMonth()->format('Y-m') }}">翌月 →</button>
        </form>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
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
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->isoFormat('MM/DD (ddd)') }}</td>
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
                    <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'attendance_id' => $attendance->id]) }}" class="details-btn">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection