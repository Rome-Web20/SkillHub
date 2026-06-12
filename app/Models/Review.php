<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_booking_id',
        'client_id',
        'worker_id',
        'rating',
        'comment',
    ];

    // Nakakonekta sa mismong transaction ticket ng booking 
    public function jobBooking()
    {
        return $this->belongsTo(JobBooking::class);
    }
}