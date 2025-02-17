@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
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
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                <input type="time" name="start_time"
                    value="{{ $pendingRequest && $pendingRequest->new_start_time ? $pendingRequest->new_start_time : optional($attendance->start_time)->format('H:i') }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
                〜
                <input type="time" name="end_time"
                    value="{{ $pendingRequest && $pendingRequest->new_end_time ? $pendingRequest->new_end_time : optional($attendance->end_time)->format('H:i') }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
            </td>
        </tr>

        @php
        $breakTimes = $pendingRequest && $pendingRequest->new_break_times ? json_decode($pendingRequest->new_break_times, true) : $attendance->breakRecords;
        @endphp
        @foreach ($breakTimes as $index => $break)
        <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>
                <input type="time"
                    value="{{ is_array($break) ? $break['start'] : optional($break->break_start)->format('H:i') }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
                〜
                <input type="time"
                    value="{{ is_array($break) ? $break['end'] : optional($break->break_end)->format('H:i') }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
            </td>
        </tr>
        @endforeach

        <tr>
            <th>備考</th>
            <td>
                <textarea name="reason" {{ $pendingRequest ? 'disabled' : '' }}>
                {{ $pendingRequest && $pendingRequest->reason ? $pendingRequest->reason : $attendance->reason }}
                </textarea>
            </td>
        </tr>
    </table>

    @if (!$pendingRequest)
    <div class="button-area">
        <form action="{{ route('attendance.request', ['id' => $attendance->id]) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">修正</button>
        </form>
    </div>
    @else
    <p class="pending-message">*承認待ちのため修正はできません。</p>
    @endif
</div>
@endsection