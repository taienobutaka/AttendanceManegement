<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤務一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sp-header.css') }}">
</head>
<body>
    <div class="header" id="header">
        <div class="title">Atte</div>
        <button type="button" class="hamburger-btn" aria-label="メニュー"><span></span><span></span><span></span></button>
        <div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>
        <div class="nav">
            <button type="button" class="nav-close-btn" aria-label="メニューを閉じる">✕</button>
            <a href="/">ホーム</a>
            <a href="/members">会員一覧</a>
            <a href="{{ route('user.attendance') }}">勤務一覧</a>
            <a href="{{ route('attendance.list') }}">日付一覧</a>
            <a href="/logout">ログアウト</a>
        </div>
    </div>
    <div class="container">
        <div class="attendance-list">
            <div class="header-row">
                <div class="header-item">名前</div>
                <div class="header-item">年月日</div>
                <div class="header-item">勤務開始</div>
                <div class="header-item">勤務終了</div>
                <div class="header-item">休憩時間</div>
                <div class="header-item">勤務時間</div>
            </div>
            @foreach($attendances as $attendance)
                <div class="data-row">
                    <div class="data-item">{{ $attendance->user->name }}</div>
                    <div class="data-item">{{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}</div>
                    <div class="data-item">@timeHmSec($attendance->start_time)</div>
                    <div class="data-item">@timeHmSec($attendance->end_time)</div>
                    <div class="data-item">@timeHmSec($attendance->total_rest_duration)</div>
                    <div class="data-item">@timeHmSec($attendance->duration)</div>
                </div>
            @endforeach
        </div>
        <div class="pagination">
            {{ $attendances->links() }}
        </div>
    </div>
    <script>
        (function() {
            var header = document.getElementById('header');
            if (!header) return;
            var btn = header.querySelector('.hamburger-btn');
            var overlay = document.getElementById('nav-overlay');
            var closeBtn = header.querySelector('.nav-close-btn');
            if (btn) btn.addEventListener('click', function() { header.classList.toggle('nav-open'); });
            if (overlay) overlay.addEventListener('click', function() { header.classList.remove('nav-open'); });
            if (closeBtn) closeBtn.addEventListener('click', function() { header.classList.remove('nav-open'); });
        })();
    </script>
</body>
</html>