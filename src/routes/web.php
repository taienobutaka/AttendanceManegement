<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RestController;

Route::get('/login', [AuthController::class, 'getLogin'])->name('login');
Route::post('/login', [AuthController::class, 'postLogin']);
Route::get('/logout', [AuthController::class, 'getLogout']);
Route::get('/register', [RegisterController::class, 'getRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'postRegister'])->name('register.post');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [AttendanceController::class, 'getIndex']);
    Route::get('/attendance/start', [AttendanceController::class, 'startAttendance']);
    Route::get('/attendance/end', [AttendanceController::class, 'endAttendance']);
    Route::get('/attendance/{num}', [AttendanceController::class, 'getAttendance']);
    Route::get('/attendance', [AttendanceController::class, 'getAttendanceList'])->name('attendance.list');
    Route::get('/break/start', [RestController::class, 'startRest']);
    Route::get('/break/end', [RestController::class, 'endRest']);
    Route::get('/members', [AttendanceController::class, 'getMembers'])->name('members.index'); // 会員一覧
    Route::get('/user', [AttendanceController::class, 'getUserAttendance'])->name('user.attendance'); // 勤怠一覧
});