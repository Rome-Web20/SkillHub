<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'worker_id',
        'service_title',
        'description',
        'location',
        'price',
        'status', // pending, accepted, completed, cancelled 
    ];

    // Kumokonekta sa Client na nag-book 
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Kumokonekta sa Worker na binu-book 
    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    // Isang booking transaction ay mayroong isang review pagkatapos ng trabaho 
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}