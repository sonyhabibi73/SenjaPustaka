<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice.
     */
    public function notice(): View|RedirectResponse
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify');
    }

    /**
     * Send a new verification email.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // Rate limit: max 3 verification emails per hour
        $recentCount = EmailVerification::where('user_id', $user->id)
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentCount >= 3) {
            return back()->with('error', 'Terlalu banyak permintaan. Silakan coba lagi dalam 1 jam.');
        }

        // Delete old tokens
        EmailVerification::where('user_id', $user->id)->delete();

        // Simpan hanya hash-nya; token mentah hanya hidup di link email,
        // sehingga bocornya isi database tidak cukup untuk mem-verifikasi akun lain.
        $rawToken = Str::random(64);

        EmailVerification::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $rawToken),
            'expires_at' => now()->addHours(24),
        ]);

        $verifyUrl = route('verification.verify', $rawToken);

        try {
            Mail::raw(
                "Halo {$user->name},\n\n".
                "Klik link di bawah untuk memverifikasi email SenjaPustaka kamu (berlaku 24 jam):\n\n".
                "{$verifyUrl}\n\n".
                "Abaikan email ini jika kamu tidak meminta verifikasi.\n\n— SenjaPustaka",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Verifikasi email kamu — SenjaPustaka');
                }
            );
        } catch (\Throwable $e) {
            report($e);
            EmailVerification::where('user_id', $user->id)->delete();

            return back()->with('error', 'Gagal mengirim email verifikasi. Silakan coba lagi nanti.');
        }

        ActivityLogger::logAuth('verification_sent', "Verification email sent to {$user->email}");

        return back()->with('success', 'Email verifikasi telah dikirim. Silakan cek inbox Anda.');
    }

    /**
     * Mark the user's email as verified.
     */
    public function verify(string $token): RedirectResponse
    {
        $verification = EmailVerification::where('token', hash('sha256', $token))
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            return redirect()->route('verification.notice')
                ->with('error', 'Link verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        $user = User::findOrFail($verification->user_id);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $verification->update(['verified_at' => now()]);

        ActivityLogger::logAuth('email_verified', "Email verified for user {$user->id}");

        return redirect()->route('dashboard')
            ->with('success', 'Email Anda berhasil diverifikasi!');
    }
}
