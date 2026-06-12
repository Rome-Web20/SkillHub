<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skills_category', // electrician, plumber, etc. 
        'is_verified',
        'base_rate',
        'average_rating',
        'description',
    ];

    // Koneksyon: Ang profile na ito ay pagmamay-ari ng isang User 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}