<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function getIndex()
    {
        $user = auth()->user(); // ユーザー情報を取得
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', auth()->id())
                                ->where('date', $today)
                                ->first();

        if ($attendance) {
            session(['attendance_started' => true]);
        } else {
            session(['attendance_started' => false, 'rest_started' => false, 'all_disabled' => false]);
        }

        // 午前0時を過ぎたらセッションをリセット
        if (now()->toTimeString() === '00:00:00') {
            session(['attendance_started' => false, 'rest_started' => false, 'all_disabled' => false]);
        }

        return view('index', compact('user')); // ユーザー情報をビューに渡す
    }

    public function startAttendance(Request $request)
    {
        $attendance = new Attendance();
        $attendance->user_id = auth()->id();
        $attendance->date = now()->toDateString();
        $attendance->start_time = now()->toTimeString();
        $attendance->save();

        session(['attendance_started' => true, 'rest_started' => false, 'all_disabled' => false]);

        return redirect('/');
    }

    public function endAttendance(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
                                ->where('date', now()->toDateString())
                                ->first();
        $attendance->end_time = now()->toTimeString();
        $attendance->save();

        session(['attendance_started' => false, 'rest_started' => false, 'all_disabled' => true]);

        return redirect('/');
    }

    public function getMembers() // 会員一覧
    {
        $members = User::paginate(5);

        foreach ($members as $member) {
            $latestAttendance = Attendance::where('user_id', $member->id)
                                           ->orderBy('date', 'desc') // 年月日の最新から表示
                                           ->first();
            $member->latest_attendance_date = $latestAttendance ? $latestAttendance->date : null;
        }

        return view('members', compact('members'));
    }

    public function getUserAttendance() // ログインユーザー勤務一覧
    {
        $user = auth()->user();
        $attendances = Attendance::where('user_id', $user->id)
                                 ->orderBy('date', 'desc') // 年月日の最新から表示
                                 ->paginate(5);

        return view('user', compact('attendances'));
    }

    public function getUserAttendanceById($id) // 特定のユーザー勤務詳細一覧
    {
        $user = User::findOrFail($id);
        $attendances = Attendance::where('user_id', $user->id)
                                 ->orderBy('date', 'desc') // 年月日の最新から表示
                                 ->paginate(5);

        return view('users', compact('user', 'attendances'));
    }

    public function getAttendanceList(Request $request) // 日付一覧
    {
        $date = $request->input('date', now()->toDateString());
        $currentDate = Carbon::parse($date);
        $prevDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        $attendances = Attendance::with('user')->whereDate('date', $currentDate)->paginate(5);

        // デバッグ情報をログに出力
        \Log::info('Current Date: ' . $currentDate);
        \Log::info('Attendances: ' . $attendances->toJson());

        return view('attendance', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

}