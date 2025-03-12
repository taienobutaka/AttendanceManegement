<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠入力画面</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startAttendanceButton = document.getElementById('start-attendance');
            const endAttendanceButton = document.getElementById('end-attendance');
            const startRestButton = document.getElementById('start-rest');
            const endRestButton = document.getElementById('end-rest');

            function updateButtonStates() {
                const attendanceStarted = sessionStorage.getItem('attendance_started') === 'true';
                const restStarted = sessionStorage.getItem('rest_started') === 'true';
                const allDisabled = sessionStorage.getItem('all_disabled') === 'true';

                startAttendanceButton.disabled = attendanceStarted || allDisabled;
                endAttendanceButton.disabled = !attendanceStarted || restStarted || allDisabled;
                startRestButton.disabled = !attendanceStarted || restStarted || allDisabled;
                endRestButton.disabled = !restStarted || allDisabled;

                startAttendanceButton.classList.toggle('disabled', attendanceStarted || allDisabled);
                endAttendanceButton.classList.toggle('disabled', !attendanceStarted || restStarted || allDisabled);
                startRestButton.classList.toggle('disabled', !attendanceStarted || restStarted || allDisabled);
                endRestButton.classList.toggle('disabled', !restStarted || allDisabled);
            }

            startAttendanceButton.addEventListener('click', function(event) {
                event.preventDefault();
                sessionStorage.setItem('attendance_started', 'true');
                sessionStorage.setItem('rest_started', 'false');
                sessionStorage.setItem('all_disabled', 'false');
                updateButtonStates();
                document.getElementById('start-attendance-form').submit();
            });

            endAttendanceButton.addEventListener('click', function(event) {
                event.preventDefault();
                sessionStorage.setItem('attendance_started', 'false');
                sessionStorage.setItem('rest_started', 'false');
                sessionStorage.setItem('all_disabled', 'true');
                updateButtonStates();
                document.getElementById('end-attendance-form').submit();
            });

            startRestButton.addEventListener('click', function(event) {
                event.preventDefault();
                sessionStorage.setItem('rest_started', 'true');
                updateButtonStates();
                document.getElementById('start-rest-form').submit();
            });

            endRestButton.addEventListener('click', function(event) {
                event.preventDefault();
                sessionStorage.setItem('rest_started', 'false');
                updateButtonStates();
                document.getElementById('end-rest-form').submit();
            });

            // 初期状態を設定
            if (!sessionStorage.getItem('attendance_started') && !sessionStorage.getItem('rest_started') && !sessionStorage.getItem('all_disabled')) {
                sessionStorage.setItem('attendance_started', 'false');
                sessionStorage.setItem('rest_started', 'false');
                sessionStorage.setItem('all_disabled', 'false');
            }

            // リセット時の状態を設定
            const lastAccessDate = sessionStorage.getItem('last_access_date');
            const currentDate = new Date().toISOString().split('T')[0];
            if (lastAccessDate !== currentDate) {
                sessionStorage.setItem('attendance_started', 'false');
                sessionStorage.setItem('rest_started', 'false');
                sessionStorage.setItem('all_disabled', 'false');
                sessionStorage.setItem('last_access_date', currentDate);
            }

            updateButtonStates();
        });
    </script>
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
            <button type="submit" id="start-attendance" class="btn">勤務開始</button>
        </form>
        <form method="GET" action="/attendance/end" id="end-attendance-form">
            <button type="submit" id="end-attendance" class="btn">勤務終了</button>
        </form>
        <form method="GET" action="/break/start" id="start-rest-form">
            <button type="submit" id="start-rest" class="btn">休憩開始</button>
        </form>
        <form method="GET" action="/break/end" id="end-rest-form">
            <button type="submit" id="end-rest" class="btn">休憩終了</button>
        </form>
    </div>
</body>
</html>