<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AttendanceInputLink;

class RegisterController extends Controller
{
    public function getRegister()
    {
        return view('register');
    }

    public function postRegister(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 認証メールを送信
        Mail::to($user->email)->send(new AttendanceInputLink($user));

        // メッセージをセッションに保存
        return back()->with('message', 'メールを送信しました。');
    }
}