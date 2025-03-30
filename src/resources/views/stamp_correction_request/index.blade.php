@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/index.css') }}">
@endsection

@section('content')
<div class="request-list-container">
    <h1>申請一覧</h1>

    <div class="request-tabs">
        <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}"
            class="tab-link {{ request('tab', 'pending') === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>

        <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}"
            class="tab-link {{ request('tab', 'pending') === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    @if (request('tab', 'pending') === 'pending')
    <div class="request-table pending">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingRequests as $request)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <form action="{{ route('attendance.show', ['id' => $request->attendance->id]) }}" method="GET">
                            <button type="submit" class="details-btn">詳細</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">承認待ちの申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    @if (request('tab', 'pending') === 'approved')
    <div class="request-table approved">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($approvedRequests as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <form action="{{ route('stamp_correction_request.show', ['id' => $request->id]) }}" method="GET">
                            <button type="submit" class="details-btn">詳細</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">承認済みの申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection