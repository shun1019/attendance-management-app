@extends('layouts.admin.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

    @if (session('success'))
    <p class="success-message">{{ session('success') }}</p>
    @endif

    <table class="attendance-detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $request->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->isoFormat('YYYY年M月D日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $request->new_start_time ? \Carbon\Carbon::parse($request->new_start_time)->format('H:i') : optional($request->attendance->start_time)->format('H:i') ?? '-' }}
                〜
                {{ $request->new_end_time ? \Carbon\Carbon::parse($request->new_end_time)->format('H:i') : optional($request->attendance->end_time)->format('H:i') ?? '-' }}
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
            <th>休憩{{ $index + 1 }}</th>
            <td>
                {{ isset($break['start']) ? \Carbon\Carbon::parse($break['start'])->format('H:i') : optional($break->break_start)->format('H:i') ?? '-' }}
                〜
                {{ isset($break['end']) ? \Carbon\Carbon::parse($break['end'])->format('H:i') : optional($break->break_end)->format('H:i') ?? '-' }}
            </td>
        </tr>
        @endforeach
        @endif

        <tr>
            <th>備考</th>
            <td>{{ $request->reason ?? '-' }}</td>
        </tr>
    </table>

    <div class="button-area">
        @if ($request->status === 0)
        <form action="{{ route('admin.stamp_correction_request.approve', ['id' => $request->id]) }}" method="POST">
            @csrf
            <button type="submit" class="primary-btn">承認</button>
        </form>
        @else
        <button class="approved-btn">承認済み</button>
        @endif
    </div>
</div>
@endsection