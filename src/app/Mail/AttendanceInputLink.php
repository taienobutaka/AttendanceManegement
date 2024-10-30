<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class AttendanceInputLink extends Mailable
{
    use SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.attendance-input-link')
                    ->subject('勤怠入力画面のご案内')
                    ->with(['user' => $this->user]);
    }
}