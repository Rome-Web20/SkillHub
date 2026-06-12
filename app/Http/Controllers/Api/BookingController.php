<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // CREATE BOOKING (In-update para saluhin ang dynamic form galing sa video)
    public function createBooking(Request $request)
    {
        // Isasave natin lahat ng galing sa pinanood nating Flutter Form screen
        DB::table('job_bookings')->insert([
            'client_id'      => $request->user()->id, 
            'worker_id'      => $request->worker_id, 
            'service_title'  => $request->category ?? 'General Service',
            'status'         => 'Pending', // Binase sa opisyal na capitalization
            'location'       => $request->location ?? 'Pasig City', // Saluhin ang text sa 'Service address'
            'description'    => $request->description ?? 'Booking request', // Saluhin ang text sa 'Notes'
            'booking_date'   => $request->booking_date, // Bagong dagdag galing sa MySQL step natin
            'time_slot'      => $request->time_slot,    // Bagong dagdag galing sa MySQL step natin
            'price'          => $request->price ?? 0,   // Ang calculated total (₱756) na ipapasa ng Flutter
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Inggat na hinayaan natin ito para mag-update pa rin ang profile status ng worker
        DB::table('worker_profiles')
            ->where('id', $request->worker_id)
            ->update(['status' => 'Busy']);

        return response()->json(['status' => true, 'message' => 'Naka-book ka na successfully!'], 200);
    }

    // GET MY BOOKINGS (Flexible para sa Client at Worker Dashboard pre)
    public function getMyBookings(Request $request)
    {
        $userId = $request->user()->id;

        // Titingnan natin kung ang humihingi ay worker profile o regular client account
        $isWorker = DB::table('worker_profiles')->where('id', $userId)->exists();

        $query = DB::table('job_bookings')
            ->join('worker_profiles', 'job_bookings.worker_id', '=', 'worker_profiles.id')
            // Gumamit tayo ng 'as category' at 'as total_price' para hindi mag-error ang lumang UI screens natin pre
            ->select(
                'job_bookings.*', 
                'job_bookings.price as total_price',
                'job_bookings.service_title as category',
                'worker_profiles.name as worker_name'
            );

        if ($isWorker) {
            // Kung worker ang naka-login, ipakita ang mga trabahong itinalaga sa kanya
            $query->where('job_bookings.worker_id', $userId);
        } else {
            // Kung client ang naka-login, ipakita ang mga binu-book niyang workers
            $query->where('job_bookings.client_id', $userId);
        }

        $bookings = $query->orderBy('job_bookings.created_at', 'desc')->get();

        return response()->json($bookings, 200);
    }

    // 🔥 BAGONG DAGDAG: UPDATE BOOKING STATUS
    // Eto ang mag-a-update sa database kapag pinindot ang mga buttons ng tracking workflow
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $booking = DB::table('job_bookings')->where('id', $id)->first();

        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
        }

        // I-update ang bagong status base sa spec sheet (Accepted, On The Way, Arrived, etc.)
        DB::table('job_bookings')
            ->where('id', $id)
            ->update([
                'status' => $request->input('status'),
                'updated_at' => now()
            ]);

        // Karagdagang logic: Kapag natapos (Completed) o Ni-reject ang booking, ibalik sa 'Available' ang worker profile
        if (in_array($request->input('status'), ['Completed', 'Rejected', 'Cancelled'])) {
            DB::table('worker_profiles')
                ->where('id', $booking->worker_id)
                ->update(['status' => 'Available']);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status successfully updated to ' . $request->input('status')
        ], 200);
    }
}