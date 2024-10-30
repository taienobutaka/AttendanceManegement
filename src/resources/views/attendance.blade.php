<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日付一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
</head>
<body>
    <div class="header">
        <div class="title">Atte</div>
        <div class="nav">
            <a href="/">ホーム</a>
            <a href="/members">会員一覧</a>
            <a href="{{ route('user.attendance') }}">勤務一覧</a>
            <a href="{{ route('attendance.list') }}">日付一覧</a>
            <a href="/logout">ログアウト</a>
        </div>
    </div>
    <div class="container">
        <div class="date-navigation">
            <form method="GET" action="{{ route('attendance.list') }}">
                <button type="submit" name="date" value="{{ $prevDate }}" class="prev-button">&lt;</button>
                <div class="date">{{ $currentDate->toDateString() }}</div>
                <button type="submit" name="date" value="{{ $nextDate }}" class="next-button">&gt;</button>
            </form>
        </div>
        <div class="attendance-list">
            <div class="header-row">
                <div class="header-item">名前</div>
                <div class="header-item">勤務開始</div>
                <div class="header-item">勤務終了</div>
                <div class="header-item">休憩時間</div>
                <div class="header-item">勤務時間</div>
            </div>
            @foreach($attendances as $attendance)
                @if(isset($attendance->user))
                    <div class="data-row">
                        <div class="data-item">{{ $attendance->user->name }}</div>
                        <div class="data-item">{{ $attendance->start_time }}</div>
                        <div class="data-item">{{ $attendance->end_time }}</div>
                        <div class="data-item">{{ $attendance->total_rest_duration }}</div>
                        <div class="data-item">{{ $attendance->duration }}</div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="pagination">
            {{ $attendances->appends(['date' => $currentDate->toDateString()])->links() }}
        </div>
    </div>
</body>
</html>