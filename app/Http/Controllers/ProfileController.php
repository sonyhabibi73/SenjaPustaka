<?php

namespace App\Http\Controllers;

use App\Services\ImageOptimizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('user.profile');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower((string) $file->guessExtension()) ?: 'jpg';
            $avatarPath = $file->storeAs('avatars', Str::random(40).'.'.$ext, 'public');

            if ($avatarPath !== false) {
                $webp = ImageOptimizer::toWebp($avatarPath, 256, 80);

                if ($webp !== null) {
                    Storage::disk('public')->delete($avatarPath);
                    $avatarPath = $webp;
                }

                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $user->avatar = $avatarPath;
            }
        }

        if ($request->filled('current_password')) {
            if (! Hash::check($data['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Kata sandi saat ini salah.',
                ]);
            }

            $user->password = $data['new_password'];
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->bio = $data['bio'] ?? null;
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui. ✨');
    }
}
