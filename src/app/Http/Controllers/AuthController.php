<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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