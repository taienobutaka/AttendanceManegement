<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠入力画面</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
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
    <div class="user-greeting">{{ $user->name }}さんお疲れ様です!</div>
    <div class="container">
        <form method="GET" action="/attendance/start" id="start-attendance-form">
            <button type="submit" id="start-attendance" class="btn {{ session('attendance_started') ? 'disabled' : '' }}" {{ session('attendance_started') ? 'disabled' : '' }}>勤務開始</button>
        </form>
        <form method="GET" action="/attendance/end" id="end-attendance-form">
            <button type="submit" id="end-attendance" class="btn {{ session('attendance_started') && !session('rest_started') && !session('all_disabled') ? '' : 'disabled' }}" {{ session('attendance_started') && !session('rest_started') && !session('all_disabled') ? '' : 'disabled' }}>勤務終了</button>
        </form>
        <form method="GET" action="/break/start" id="start-rest-form">
            <button type="submit" id="start-rest" class="btn {{ session('attendance_started') && !session('rest_started') && !session('all_disabled') ? '' : 'disabled' }}" {{ session('attendance_started') && !session('rest_started') && !session('all_disabled') ? '' : 'disabled' }}>休憩開始</button>
        </form>
        <form method="GET" action="/break/end" id="end-rest-form">
            <button type="submit" id="end-rest" class="btn {{ session('rest_started') && !session('all_disabled') ? '' : 'disabled' }}" {{ session('rest_started') && !session('all_disabled') ? '' : 'disabled' }}>休憩終了</button>
        </form>
    </div>
</body>
</html>