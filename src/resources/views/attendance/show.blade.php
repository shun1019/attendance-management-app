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

    {{-- 申請中メッセージ --}}
    @if ($pendingRequest)
    <p class="pending-message">修正申請中（管理者の承認待ち）</p>
    @else
    <form action="{{ route('attendance.request', ['id' => $attendance->id]) }}" method="POST">
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
                    <input type="time" name="start_time" value="{{ optional($attendance->start_time)->format('H:i') }}">
                    〜
                    <input type="time" name="end_time" value="{{ optional($attendance->end_time)->format('H:i') }}">
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    @foreach ($attendance->breakRecords as $break)
                    <div>
                        <input type="time" name="break_times[{{ $loop->index }}][start]" value="{{ optional($break->break_start)->format('H:i') }}">
                        〜
                        <input type="time" name="break_times[{{ $loop->index }}][end]" value="{{ optional($break->break_end)->format('H:i') }}">
                    </div>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td><textarea name="reason" required></textarea></td>
            </tr>
        </table>

        <div class="button-area">
            <button type="submit" class="btn btn-primary">修正</button>
        </div>
    </form>
    @endif
</div>
@endsection