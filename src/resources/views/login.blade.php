<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン画面</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sp-header.css') }}">
</head>
<body>
    <div class="header" id="header">
        <div class="title">Atte</div>
        <button type="button" class="hamburger-btn" aria-label="メニュー"><span></span><span></span><span></span></button>
        <div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>
        <div class="nav">
            <button type="button" class="nav-close-btn" aria-label="メニューを閉じる">✕</button>
            <a href="/register">会員登録</a>
        </div>
    </div>
    <div class="login-container">
        <h2>ログイン</h2>
        @if ($errors->has('login'))
            <div class="error">{{ $errors->first('login') }}</div>
        @endif
        <form action="/login" method="POST" novalidate>
            @csrf
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="メールアドレス">
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="パスワード">
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit">ログイン</button>
        </form>
        <div class="register-info">
            アカウントをお持ちでない方はこちらから
        </div>
        <div class="register-link">
            <a href="/register">会員登録</a>
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
    @include('partials.chatbot')
</body>
</html>