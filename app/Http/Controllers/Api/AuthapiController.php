<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\Booking; // 🚀 IDINAGDAG NATIN ITO PARA MAKILALA ANG BOOKING MODEL
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Idinagdag para sa DB Queries

class AuthapiController extends Controller
{
    // 1. REGISTER API
    public function register(Request $request)
    {
        // I-validate ang mga sumasapit na data mula sa frontend
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'nullable|string',
            'role' => 'required|in:client,worker',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // I-save ang User sa database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'role' => $request->role,
        ]);

        // KUNG ANG NAG-REGISTER AY WORKER: Awtomatiko nating gagawan ng walang lamang WorkerProfile
        if ($user->role === 'worker') {
            WorkerProfile::create([
                'user_id' => $user->id,
                'skills_category' => null, // I-o-update na lang nila ito sa profile setup nila
                'is_verified' => false,    // Kailangan ng admin approval mamaya
            ]);
        }

        // Gawan ng Sanctum Token para diretsong naka-login pagkatapos mag-register
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    // 2. LOGIN API
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Hanapin ang email sa users table
        $user = User::where('email', $request->email)->first();

        // I-verify kung tama ang password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid login credentials'
            ], 401);
        }

        // Gawan ng bagong token para sa session na ito
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone_number' => $user->phone_number,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    // 3. LOGOUT API
    public function logout(Request $request)
    {
        // Burahin ang kasalukuyang token para ma-expire ang session
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    // 🚀 4. GET WORKERS BY CATEGORY (NAITAMA NA ANG QUERY PRE!)
    public function getWorkersByCategory($category)
    {
        try {
            // Gumamit tayo ng Join para pagdikitin ang 'users' at 'worker_profiles' table
            $workers = DB::table('users')
                ->join('worker_profiles', 'users.id', '=', 'worker_profiles.user_id')
                ->select(
                    'users.id', 
                    'users.name', 
                    'users.phone_number', 
                    'worker_profiles.skills_category',
                    // Idagdag natin ang mga ito bilang fallback strings para hindi mag-crash si Flutter UI
                    DB::raw("'Pasig City' as location"), 
                    DB::raw("'5.0' as rating"), 
                    DB::raw("'Available' as status")
                )
                ->where('worker_profiles.skills_category', $category)
                ->get();

            return response()->json($workers, 200);

        } catch (\Exception $e) {
            // Kung may sumablay man sa table names, ibabato nito ang totoong error message
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🚀 5. UPDATE WORKER PROFILE (IDINAGDAG NA PRE!)
    public function updateWorkerProfile(Request $request)
    {
        // I-validate kung may pinadalang kategorya mula sa Flutter
        $validator = Validator::make($request->all(), [
            'skills_category' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Kunin ang kasalukuyang naka-login na user gamit ang Sanctum token
            $user = $request->user();

            // Siguraduhing worker talaga ang nag-a-access nito
            if ($user->role !== 'worker') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Client accounts cannot perform this action.'
                ], 403);
            }

            // I-update o gumawa ng profile sa worker_profiles table
            $profile = WorkerProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['skills_category' => $request->skills_category]
            );

            return response()->json([
                'status' => true,
                'message' => 'Worker profile updated successfully!',
                'data' => $profile
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🚀 6. CREATE BOOKING API (IDINAGDAG NA PARA SA HAKBANG 3!)
    public function createBooking(Request $request)
    {
        // I-validate ang input mula sa Flutter app
        $validator = Validator::make($request->all(), [
            'worker_id' => 'required|integer',
            'category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Kunin ang ID ng kasalukuyang naka-login na Client gamit ang Sanctum Token
            $clientId = $request->user()->id;

            // I-insert ang booking sa database
            $booking = Booking::create([
                'client_id' => $clientId,
                'worker_id' => $request->worker_id,
                'category' => $request->category,
                'status' => 'Pending', // Default status pre
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Booking request sent successfully!',
                'booking' => $booking
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}