<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録画面</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="header">
        <div class="title">Atte</div>
    </div>
    <div class="register-container">
        <h2>会員登録</h2>
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <input type="text" id="name" name="name" placeholder="名前" value="{{ old('name') }}" required>
                @error('name')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}" required>
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="パスワード" required>
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="確認用パスワード" required>
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
</body>
</html>