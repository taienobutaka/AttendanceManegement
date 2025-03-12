<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class ResetSessionAtMidnight
{
    public function handle($request, Closure $next)
    {
        $currentDate = now()->toDateString();
        $lastAccessDate = Session::get('last_access_date');

        if ($lastAccessDate !== $currentDate) {
            Session::flush();
            Session::put('attendance_started', 'false');
            Session::put('rest_started', 'false');
            Session::put('all_disabled', 'false');
            Session::put('last_access_date', $currentDate);
        }

        return $next($request);
    }
}
