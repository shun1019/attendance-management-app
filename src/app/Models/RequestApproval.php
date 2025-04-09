<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute()
    {
        return [
            0 => '承認待ち',
            1 => '承認済み',
        ];
    }
}
