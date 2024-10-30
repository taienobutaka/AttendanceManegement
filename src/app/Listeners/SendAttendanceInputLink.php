<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\AttendanceInputLink;

class SendAttendanceInputLink
{
    public function handle(Registered $event)
    {
        // ユーザーのメールアドレスに勤怠入力リンクを送信
        Mail::to($event->user->email)->send(new AttendanceInputLink($event->user));
    }
}