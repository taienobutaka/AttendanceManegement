<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function getLogin()
    {
        return view('login');
    }

    public function postLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // ログイン時にセッションを初期化
            Session::put('attendance_started', 'false');
            Session::put('rest_started', 'false');
            Session::put('all_disabled', 'false');
            Session::put('last_access_date', now()->toDateString());

            return redirect()->intended('/');
        }

        return redirect('/login')->withErrors([
            'login' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }

    public function getLogout()
    {
        Auth::logout();
        return redirect('/login');
    }
}