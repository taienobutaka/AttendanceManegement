<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/resetl.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/members.css') }}">
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
        <div class="member-list">
            <div class="header-row">
                <div class="header-item">名前</div>
                <div class="header-item">最新勤務日</div>
                <div class="header-item">勤務一覧</div>
            </div>
            @foreach($members as $member)
                <div class="data-row">
                    <div class="data-item">{{ $member->name }}</div>
                    <div class="data-item">
                        {{ $member->latest_attendance_date ? \Carbon\Carbon::parse($member->latest_attendance_date)->format('Y-m-d') : '' }}
                    </div>
                    <div class="data-item">
                        <a href="{{ route('user.attendance.byId', ['id' => $member->id]) }}" class="detail-link">詳細</a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="pagination">
            {{ $members->links() }}
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