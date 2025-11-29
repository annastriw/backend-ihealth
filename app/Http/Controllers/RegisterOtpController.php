<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class RegisterOtpController extends Controller
{
    private $otpTTLMinutes = 5;       // OTP berlaku 5 menit
    private $otpCooldownSeconds = 30; // Waktu cooldown resend
    private $maxAttempts = 5;         // Max percobaan OTP salah
    private $maxResend = 5;           // Max resend

    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    // =====================================================
    // 1. REGISTER INIT (KIRIM OTP PERTAMA)
    // =====================================================
    public function registerInit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'username' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'password' => 'required|string|min:6|confirmed',
            'disease_type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        // Cek duplikasi user
        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Email sudah digunakan'], 400);
        }
        if (User::where('username', $data['username'])->exists()) {
            return response()->json(['message' => 'Username sudah digunakan'], 400);
        }
        if (User::where('phone_number', $data['phone_number'])->exists()) {
            return response()->json(['message' => 'Nomor telepon sudah digunakan'], 400);
        }

        // Cek cooldown
        $cooldownKey = "otp_cooldown:register:{$data['email']}";
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'message' => 'Tunggu sebelum meminta OTP lagi',
                'retry_after' => Cache::get($cooldownKey . '_ttl')
            ], 429);
        }

        Cache::put($cooldownKey, true, $this->otpCooldownSeconds);
        Cache::put($cooldownKey . '_ttl', $this->otpCooldownSeconds, $this->otpCooldownSeconds);

        // Simpan data sementara
        Cache::put("register:data:{$data['email']}", $data, ($this->otpTTLMinutes + 5) * 60);

        // Hapus OTP lama
        Otp::where('email', $data['email'])->where('type', 'register')->delete();

        // Buat OTP baru
        $plainOtp = $this->generateOtp();
        Otp::create([
            'id' => (string) Str::uuid(),
            'email' => $data['email'],
            'otp_hash' => Hash::make($plainOtp),
            'type' => 'register',
            'attempts' => 0,
            'resend_count' => 0,
            'is_used' => false,
            'is_expired' => false,
            'expires_at' => now()->addMinutes($this->otpTTLMinutes),
        ]);

        Mail::to($data['email'])->send(new OtpMail($plainOtp, 'Kode OTP Pendaftaran', $data['name']));

        return response()->json([
            'message' => 'OTP telah dikirim',
            'email' => $data['email']
        ]);
    }

    // =====================================================
    // 2. REGISTER RESEND
    // =====================================================
    public function registerResend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $email = $request->email;
        $data = Cache::get("register:data:{$email}");

        if (!$data) return response()->json(['message' => 'Tidak ada proses pendaftaran'], 400);

        $latestOtp = Otp::where('email', $email)
            ->where('type', 'register')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestOtp && $latestOtp->resend_count >= $this->maxResend) {
            Otp::where('email', $email)->where('type', 'register')->delete();
            Cache::forget("register:data:{$email}");
            Cache::forget("register:verified:{$email}");
            return response()->json(['message' => 'Batas resend habis, daftar ulang'], 429);
        }

        $cooldownKey = "otp_cooldown:register:{$email}";
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'message' => 'Tunggu sebelum mengirim ulang',
                'retry_after' => Cache::get($cooldownKey . '_ttl')
            ], 429);
        }

        Cache::put($cooldownKey, true, $this->otpCooldownSeconds);
        Cache::put($cooldownKey . '_ttl', $this->otpCooldownSeconds, $this->otpCooldownSeconds);

        $newOtp = $this->generateOtp();
        Otp::create([
            'id' => (string) Str::uuid(),
            'email' => $email,
            'otp_hash' => Hash::make($newOtp),
            'type' => 'register',
            'attempts' => $latestOtp ? $latestOtp->attempts : 0,
            'resend_count' => $latestOtp ? $latestOtp->resend_count + 1 : 1,
            'is_used' => false,
            'is_expired' => false,
            'expires_at' => now()->addMinutes($this->otpTTLMinutes),
        ]);

        Mail::to($email)->send(new OtpMail($newOtp, 'OTP Baru Pendaftaran', $data['name']));

        return response()->json([
            'message' => 'OTP baru telah dikirim',
            'resend_count' => $latestOtp ? $latestOtp->resend_count + 1 : 1
        ]);
    }

    // =====================================================
    // 3. REGISTER VERIFY
    // =====================================================
    public function registerVerify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6'
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 400);

        $email = $request->email;
        $inputOtp = $request->otp;

        $otp = Otp::where('email', $email)
            ->where('type', 'register')
            ->where('is_used', false)
            ->where('is_expired', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otp)
            return response()->json(['message' => 'OTP tidak ditemukan / kadaluarsa'], 400);

        // Cek expired sebenarnya
        if ($otp->expires_at && now()->gt($otp->expires_at)) {
            $otp->update(['is_expired' => true]);
            return response()->json(['message' => 'OTP sudah kadaluarsa'], 400);
        }

        // OTP salah
        if (!Hash::check($inputOtp, $otp->otp_hash)) {
            $otp->increment('attempts');

            if ($otp->attempts + 1 >= $this->maxAttempts) {
                $otp->update(['is_expired' => true]);
                Cache::forget("register:data:{$email}");
                return response()->json([
                    'message' => 'Terlalu banyak percobaan salah, daftar ulang',
                    'remaining_attempts' => 0
                ], 429);
            }

            return response()->json([
                'message' => 'Kode OTP salah',
                'remaining_attempts' => $this->maxAttempts - ($otp->attempts + 1)
            ], 400);
        }

        // OTP benar
        $otp->update(['is_used' => true]);

        Cache::put("register:verified:{$email}", true, ($this->otpTTLMinutes + 5) * 60);

        return response()->json([
            'message' => 'OTP valid',
            'remaining_attempts' => $this->maxAttempts - $otp->attempts
        ]);
    }

    // =====================================================
    // 4. REGISTER COMPLETE
    // =====================================================
    public function registerComplete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 400);

        $email = $request->email;

        if (!Cache::get("register:verified:{$email}")) {
            return response()->json(['message' => 'OTP belum diverifikasi'], 400);
        }

        $data = Cache::get("register:data:{$email}");
        if (!$data)
            return response()->json(['message' => 'Data kadaluarsa, ulangi pendaftaran'], 400);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'phone_number' => $data['phone_number'],
            'disease_type' => $data['disease_type'],
            'password' => Hash::make($data['password']),
        ]);

        Cache::forget("register:data:{$email}");
        Cache::forget("register:verified:{$email}");
        Otp::where('email', $email)->where('type', 'register')->delete();

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }
}
