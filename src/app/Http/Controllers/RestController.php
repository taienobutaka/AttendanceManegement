<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;

class RestController extends Controller
{
    public function startRest(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
                                ->where('date', now()->toDateString())
                                ->first();

        if ($attendance) {
            $rest = new Rest();
            $rest->attendance_id = $attendance->id;
            $rest->start_time = now()->toTimeString();
            $rest->save();
        }

        session(['rest_started' => true]);

        return redirect('/');
    }

    public function endRest(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
                                ->where('date', now()->toDateString())
                                ->first();

        if ($attendance) {
            $rest = Rest::where('attendance_id', $attendance->id)
                        ->whereNull('end_time')
                        ->first();

            if ($rest) {
                $rest->end_time = now()->toTimeString();
                $rest->save();
            }
        }

        session(['rest_started' => false]);

        return redirect('/');
    }
}