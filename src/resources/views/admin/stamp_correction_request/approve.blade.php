@extends('layouts.app')

@section('title', '勤怠詳細(申請承認)')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/approve.css') }}">
@endsection

@section('content')
<div class="request-approval-container">
    <h1>勤怠詳細</h1>

    <table class="request-detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $request->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>
                @php
                $date = \Carbon\Carbon::parse($request->attendance->work_date);
                $year = $date->format('Y');
                $monthDay = $date->format('n月j日');
                @endphp
                <span class="request-date-year">{{ $year }}年</span>
                <span class="date-month-day">{{ $monthDay }}</span>
            </td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                <div class="request-time-view">
                    {{ $request->new_start_time ? \Carbon\Carbon::parse($request->new_start_time)->format('H:i') : optional($request->attendance->start_time)->format('H:i') ?? '-' }}
                    <span class="time-separator">〜</span>
                    {{ $request->new_end_time ? \Carbon\Carbon::parse($request->new_end_time)->format('H:i') : optional($request->attendance->end_time)->format('H:i') ?? '-' }}
                </div>
            </td>
        </tr>

        @php
        $breakTimes = !empty($request->new_break_times)
        ? json_decode($request->new_break_times, true)
        : $request->attendance->breakRecords;
        @endphp

        @if (empty($breakTimes) || count($breakTimes) === 0)
        <tr>
            <th>休憩</th>
            <td>-</td>
        </tr>
        @else
        @foreach ($breakTimes as $index => $break)
        <tr>
            <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
            <td>
                <div class="request-time-view">
                    {{ isset($break['start']) ? \Carbon\Carbon::parse($break['start'])->format('H:i') : optional($break->break_start)->format('H:i') ?? '-' }}
                    <span class="time-separator">〜</span>
                    {{ isset($break['end']) ? \Carbon\Carbon::parse($break['end'])->format('H:i') : optional($break->break_end)->format('H:i') ?? '-' }}
                </div>
            </td>
        </tr>
        @endforeach
        @endif

        <tr>
            <th>備考</th>
            <td class="request-reason">{{ $request->reason ?? '-' }}</td>
        </tr>
    </table>

    <div class="button-area">
        @if ($request->status === 0)
        <form action="{{ route('stamp_correction_request.approve', ['attendance_correct_request' => $request->id]) }}" method="POST">
            @csrf
            <button type="submit" class="primary-btn">承認</button>
        </form>
        @else
        <button class="approved-btn">承認済み</button>
        @endif
    </div>
</div>
@endsection