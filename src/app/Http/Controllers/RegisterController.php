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

        try {
            Mail::to($user->email)->send(new AttendanceInputLink($user));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Registration mail failed: ' . $e->getMessage());
        }

        Auth::login($user);

        return redirect('/');
    }
}