@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

    <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PUT')

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
                    <input type="time" name="start_time" value="{{ old('start_time', optional($attendance->start_time)->format('H:i') ?? '') }}">
                    〜
                    <input type="time" name="end_time" value="{{ old('end_time', optional($attendance->end_time)->format('H:i') ?? '') }}">
                    @error('end_time')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            @php
            $breakTimes = $attendance->breakRecords;
            @endphp

            @foreach ($breakTimes as $index => $break)
            <tr>
                <th>{{ $index === 0 ? '休憩' : '休憩' . $index }}</th>
                <td>
                    <input type="time" name="break_times[{{ $index }}][start]"
                        value="{{ old("break_times.$index.start", optional($break->break_start)->format('H:i') ?? '') }}">
                    〜
                    <input type="time" name="break_times[{{ $index }}][end]"
                        value="{{ old("break_times.$index.end", optional($break->break_end)->format('H:i') ?? '') }}">
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
                    <textarea name="reason">{{ old('reason', $attendance->reason ?? '') }}</textarea>
                    @error('reason')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        <div class="button-area">
            <button type="submit" class="primary-btn">修正</button>
        </div>
    </form>
</div>
@endsection