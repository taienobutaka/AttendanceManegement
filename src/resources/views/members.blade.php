<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/members.css') }}">
</head>
<body>
    <div class="header">
        <div class="title">Atte</div>
        <div class="nav">
            <a href="/">ホーム</a>
            <a href="/members">会員一覧</a>
            <a href="{{ route('user.attendance') }}">勤務一覧</a> <!-- 追加 -->
            <a href="{{ route('attendance.list') }}">日付一覧</a>
            <a href="/logout">ログアウト</a>
        </div>
    </div>
    <div class="container">
        <div class="member-list">
            <div class="header-row">
                <div class="header-item">名前</div>
                <div class="header-item">最新勤務日</div>
            </div>
            @foreach($members as $member)
                <div class="data-row">
                    <div class="data-item">{{ $member->name }}</div>
                    <div class="data-item">
                        {{ $member->latest_attendance_date ? \Carbon\Carbon::parse($member->latest_attendance_date)->format('Y-m-d') : '' }}
                    </div>
                </div>
            @endforeach
        </div>
        <div class="pagination">
            {{ $members->links() }}
        </div>
    </div>
</body>
</html>