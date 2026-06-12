<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // 🚀 Pinapayagan natin si Laravel na i-mass assign ang mga columns na ito galing sa API request
    protected $fillable = [
        'client_id',
        'worker_id',
        'category',
        'status',
    ];
}