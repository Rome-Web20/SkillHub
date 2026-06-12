<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // CREATE BOOKING
    public function createBooking(Request $request)
    {
        DB::table('job_bookings')->insert([
            'client_id'      => $request->user()->id, 
            'worker_id'      => $request->worker_id, 
            'service_title'  => $request->category ?? 'General Service',
            'status'         => 'pending',
            'location'       => 'Pasig City', 
            'description'    => 'Booking request',
            'price'          => 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('worker_profiles')
            ->where('id', $request->worker_id)
            ->update(['status' => 'Busy']);

        return response()->json(['status' => true, 'message' => 'Naka-book ka na successfully!'], 200);
    }

    // GET MY BOOKINGS
    public function getMyBookings(Request $request)
    {
        $bookings = DB::table('job_bookings')
            ->join('worker_profiles', 'job_bookings.worker_id', '=', 'worker_profiles.id')
            ->select('job_bookings.*', 'worker_profiles.name as worker_name')
            ->where('job_bookings.client_id', $request->user()->id)
            ->orderBy('job_bookings.created_at', 'desc')
            ->get();

        return response()->json($bookings, 200);
    }
}