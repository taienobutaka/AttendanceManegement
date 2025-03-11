<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠入力画面のご案内</title>
</head>
<body>
    <p>{{ $user->name }}様</p>
    <p>会員登録ありがとうございます。</p>
    <p>以下のリンクから勤怠入力画面にアクセスしてください。</p>
    <p><a href="{{ url('/') }}">勤怠入力画面</a></p>
</body>
</html>