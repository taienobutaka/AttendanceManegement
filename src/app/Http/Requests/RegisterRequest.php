<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:8|max:191|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名前を入力してください。',
            'name.string' => '名前は文字列でなければなりません。',
            'name.max' => '名前は191文字以内でなければなりません。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.string' => 'メールアドレスは文字列でなければなりません。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'email.max' => 'メールアドレスは191文字以内でなければなりません。',
            'email.unique' => 'このメールアドレスは既に登録されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.string' => 'パスワードは文字列でなければなりません。',
            'password.min' => 'パスワードは8文字以上でなければなりません。',
            'password.max' => 'パスワードは191文字以内でなければなりません。',
            'password.confirmed' => 'パスワード確認が一致しません。',
        ];
    }
}