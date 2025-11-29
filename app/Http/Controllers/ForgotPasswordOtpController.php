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

class ForgotPasswordOtpController extends Controller
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
    // 1. FORGOT PASSWORD INIT
    // =====================================================
    public function forgotPasswordInit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $email = $request->email;

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan'], 404);
        }

        // Cek cooldown
        $cooldownKey = "otp_cooldown:forgot:{$email}";
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'message' => 'Tunggu sebelum meminta OTP lagi',
                'retry_after' => Cache::get($cooldownKey . '_ttl')
            ], 429);
        }

        Cache::put($cooldownKey, true, $this->otpCooldownSeconds);
        Cache::put($cooldownKey . '_ttl', $this->otpCooldownSeconds, $this->otpCooldownSeconds);

        // Hapus OTP lama
        Otp::where('email', $email)->where('type', 'forgot')->delete();

        // Buat OTP baru
        $plainOtp = $this->generateOtp();
        Otp::create([
            'id' => (string) Str::uuid(),
            'email' => $email,
            'otp_hash' => Hash::make($plainOtp),
            'type' => 'forgot',
            'attempts' => 0,
            'resend_count' => 0,
            'is_used' => false,
            'is_expired' => false,
            'expires_at' => now()->addMinutes($this->otpTTLMinutes),
        ]);

        Mail::to($email)->send(new OtpMail($plainOtp, 'Kode OTP Lupa Password', $user->name));

        return response()->json([
            'message' => 'OTP telah dikirim',
            'email' => $email
        ]);
    }

    // =====================================================
    // 2. FORGOT PASSWORD RESEND
    // =====================================================
    public function forgotPasswordResend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $email = $request->email;

        $user = User::where('email', $email)->first();
        if (!$user) return response()->json(['message' => 'Email tidak ditemukan'], 404);

        $latestOtp = Otp::where('email', $email)
            ->where('type', 'forgot')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestOtp && $latestOtp->resend_count >= $this->maxResend) {
            Otp::where('email', $email)->where('type', 'forgot')->delete();
            Cache::forget("forgot:verified:{$email}");
            return response()->json(['message' => 'Batas resend habis, ulangi proses'], 429);
        }

        $cooldownKey = "otp_cooldown:forgot:{$email}";
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
            'type' => 'forgot',
            'attempts' => $latestOtp ? $latestOtp->attempts : 0,
            'resend_count' => $latestOtp ? $latestOtp->resend_count + 1 : 1,
            'is_used' => false,
            'is_expired' => false,
            'expires_at' => now()->addMinutes($this->otpTTLMinutes),
        ]);

        Mail::to($email)->send(new OtpMail($newOtp, 'OTP Baru Lupa Password', $user->name));

        return response()->json([
            'message' => 'OTP baru telah dikirim',
            'resend_count' => $latestOtp ? $latestOtp->resend_count + 1 : 1
        ]);
    }

    // =====================================================
    // 3. FORGOT PASSWORD VERIFY
    // =====================================================
    public function forgotPasswordVerify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $email = $request->email;
        $inputOtp = $request->otp;

        $otp = Otp::where('email', $email)
            ->where('type', 'forgot')
            ->where('is_used', false)
            ->where('is_expired', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'OTP tidak ditemukan / kadaluarsa'], 400);
        }

        if ($otp->expires_at && now()->gt($otp->expires_at)) {
            $otp->update(['is_expired' => true]);
            return response()->json(['message' => 'OTP sudah kadaluarsa'], 400);
        }

        if (!Hash::check($inputOtp, $otp->otp_hash)) {
            $otp->increment('attempts');

            if ($otp->attempts + 1 >= $this->maxAttempts) {
                $otp->update(['is_expired' => true]);
                Cache::forget("forgot:verified:{$email}");
                return response()->json([
                    'message' => 'Terlalu banyak percobaan salah, ulangi proses',
                    'remaining_attempts' => 0
                ], 429);
            }

            return response()->json([
                'message' => 'Kode OTP salah',
                'remaining_attempts' => $this->maxAttempts - ($otp->attempts + 1)
            ], 400);
        }

        $otp->update(['is_used' => true]);
        Cache::put("forgot:verified:{$email}", true, ($this->otpTTLMinutes + 5) * 60);

        return response()->json([
            'message' => 'OTP valid',
            'remaining_attempts' => $this->maxAttempts - $otp->attempts
        ]);
    }

    // =====================================================
    // 4. FORGOT PASSWORD COMPLETE
    // =====================================================
    public function forgotPasswordComplete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $email = $request->email;

        if (!Cache::get("forgot:verified:{$email}")) {
            return response()->json(['message' => 'OTP belum diverifikasi'], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Cache::forget("forgot:verified:{$email}");
        Otp::where('email', $email)->where('type', 'forgot')->delete();

        $token = JWTAuth::fromUser($user); // auto login

        return response()->json([
            'message' => 'Password berhasil diubah',
            'user' => $user,
            'token' => $token
        ]);
    }
}
