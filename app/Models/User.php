<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role', // 'client', 'worker', 'admin' 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Koneksyon: Ang User ay mayroong isang Worker Profile 
    public function workerProfile()
    {
        return $this->hasOne(WorkerProfile::class);
    }

    // Koneksyon: Ang User (bilang Client) ay pwedeng magkaroon ng maraming bookings 
    public function bookingsAsClient()
    {
        return $this->hasMany(JobBooking::class, 'client_id');
    }

    // Koneksyon: Ang User (bilang Worker) ay pwedeng makatanggap ng maraming bookings 
    public function bookingsAsWorker()
    {
        return $this->hasMany(JobBooking::class, 'worker_id');
    }
}
