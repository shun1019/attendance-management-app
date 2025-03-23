@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

    <form action="{{ route('attendance.request', $attendance->id) }}" method="POST">
        @csrf
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
                        value="{{ old('start_time', $pendingRequest && $pendingRequest->new_start_time ? $pendingRequest->new_start_time : optional($attendance->start_time)->format('H:i')) }}"
                        {{ $pendingRequest ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="end_time"
                        value="{{ old('end_time', $pendingRequest && $pendingRequest->new_end_time ? $pendingRequest->new_end_time : optional($attendance->end_time)->format('H:i')) }}"
                        {{ $pendingRequest ? 'disabled' : '' }}>

                    @error('end_time')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            @php
            $breakTimes = $pendingRequest && !empty($pendingRequest->new_break_times)
            ? json_decode($pendingRequest->new_break_times, true)
            : $attendance->breakRecords;
            @endphp
            @foreach ($breakTimes as $index => $break)
            <tr>
                <th>{{ $index === 0 ? '休憩' : '休憩' . $index }}</th>
                <td>
                    <input type="time" name="break_times[{{ $index }}][start]"
                        value="{{ old("break_times.$index.start", is_array($break) ? $break['start'] : optional($break->break_start)->format('H:i')) }}"
                        {{ $pendingRequest ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="break_times[{{ $index }}][end]"
                        value="{{ old("break_times.$index.end", is_array($break) ? $break['end'] : optional($break->break_end)->format('H:i')) }}"
                        {{ $pendingRequest ? 'disabled' : '' }}>

                    @error("break_times.$index.start")
                    <div class="error">{{ $message }}</div>
                    @enderror
                    @error("break_times.$index.end")
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @endforeach

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="reason" {{ $pendingRequest ? 'disabled' : '' }}>{{ old('reason', $pendingRequest && $pendingRequest->reason ? $pendingRequest->reason : $attendance->reason) }}</textarea>

                    @error('reason')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        @if (!$pendingRequest)
        <div class="button-area">
            <button type="submit" class="primary-btn">修正</button>
        </div>
        @else
        <p class="pending-message">*承認待ちのため修正はできません。</p>
        @endif
    </form>
</div>
@endsection