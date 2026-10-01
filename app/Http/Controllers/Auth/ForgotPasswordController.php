<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.passwords.email');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_THROTTLED) {
            return back()->with('error', 'Terlalu banyak permintaan. Silakan coba lagi dalam beberapa menit.');
        }

        // Log untuk audit (termasuk email yang tidak terdaftar).
        ActivityLogger::logAuth('password_reset_requested', "Password reset requested for {$request->email}");

        // Pesan yang sama untuk email terdaftar maupun tidak — mencegah
        // user enumeration lewat perbedaan respons.
        return back()->with('success', 'Jika email terdaftar di sistem kami, link reset password telah dikirim. Silakan cek inbox.');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();

                ActivityLogger::logAuth('password_reset_completed', "Password reset completed for user {$user->id}");
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
        }

        return back()->with('error', 'Token reset password tidak valid atau sudah kedaluwarsa.');
    }
}
