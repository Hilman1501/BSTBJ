<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function loginByHmac(Request $request)
    {
        $payloadEncoded = $request->query('payload');
        $signature = $request->query('signature');

        if (!$payloadEncoded || !$signature) {
            return response()->json(['message' => 'Missing payload or signature'], 400);
        }

        $payloadJson = base64_decode($payloadEncoded);
        $payload = json_decode($payloadJson, true);

        if (!is_array($payload) || !isset($payload['email'], $payload['timestamp'])) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Cek selisih waktu maksimal 5 menit
        if (abs(now()->timestamp - $payload['timestamp']) > 300) {
            return response()->json(['message' => 'Expired payload'], 401);
        }

        // Ambil secret sesuai aplikasi
        $sharedSecret = env('SHARED_SECRET_SUPERAPPS_BSTBJ', 'Bstbj2026');
        Log::info($sharedSecret);
        Log::info($payload);
        Log::info($payloadJson);

        $expectedSignature = hash_hmac('sha256', $payloadJson, $sharedSecret);
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // Login atau buat user baru
        $user = User::firstOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'],
                'role' => $payload['role'],
                'department' => $payload['department'],
                'id_card' => $payload['id_card'],
                'password' => Hash::make(Str::random(16)), // dummy password
            ]
        );

        Auth::login($user);
        // Simpan manual user_id ke session
        session(['user_id' => $user->id]);

        logger('User logged in:', [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'department' => $user->department,
            'id_card' => $user->id_card,
            'name' => $user->name,
            'session_id' => session()->getId(),
        ]);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('https://hub.lautanairindonesia.com/homeapps')->with('message', 'Berhasil logout');
    }
}
