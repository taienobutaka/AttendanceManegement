<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン画面</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="header">
        <div class="title">Atte</div>
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
</body>
</html>