<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'start_time', 'end_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function getDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            return $end->diff($start)->format('%H:%I:%S');
        }
        return null;
    }

    public function getTotalRestDurationAttribute()
    {
        $totalSeconds = $this->rests->sum('duration');
        return gmdate('H:i:s', $totalSeconds);
    }
}