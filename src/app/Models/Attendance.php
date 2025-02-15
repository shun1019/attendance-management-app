<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakRecords()
    {
        return $this->hasMany(BreakRecord::class);
    }

    public function getTotalBreakTime()
    {
        return $this->breakRecords->sum(function ($break) {
            if ($break->break_start && $break->break_end) {
                return Carbon::parse($break->break_end)->diffInSeconds(Carbon::parse($break->break_start));
            }
            return 0;
        });
    }

    public function getWorkDuration()
    {
        if ($this->start_time && $this->end_time) {
            return Carbon::parse($this->end_time)->diffInSeconds(Carbon::parse($this->start_time)) - $this->getTotalBreakTime();
        }
        return 0;
    }

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'work_date' => 'date',
    ];
}
