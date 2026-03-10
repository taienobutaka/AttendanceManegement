<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録画面</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sp-header.css') }}">
</head>
<body>
    <div class="header" id="header">
        <div class="title">Atte</div>
        <button type="button" class="hamburger-btn" aria-label="メニュー"><span></span><span></span><span></span></button>
        <div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>
        <div class="nav">
            <button type="button" class="nav-close-btn" aria-label="メニューを閉じる">✕</button>
            <a href="/login">ログイン</a>
        </div>
    </div>
    <div class="register-container">
        <h2>会員登録</h2>
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        <form action="{{ route('register.post') }}" method="POST" novalidate>
            @csrf
            <div class="form-group">
                <input type="text" id="name" name="name" placeholder="名前" value="{{ old('name') }}">
                @error('name')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}">
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
            <div class="form-group">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="確認用パスワード">
                @error('password_confirmation')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit">会員登録</button>
        </form>
        <div class="register-info">
            アカウントをお持ちの方はこちらから
        </div>
        <div class="register-link">
            <a href="/login">ログイン</a>
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