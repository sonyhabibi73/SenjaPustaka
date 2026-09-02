<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $data['email']]);
        $subscriber->name = $data['name'] ?? $subscriber->name;
        $subscriber->subscribed = true;
        $subscriber->token ??= Str::random(40);
        $subscriber->save();

        return back()->with('success', 'Kamu berhasil berlangganan newsletter! 📬');
    }

    public function toggle(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $user->email],
            ['name' => $user->name, 'token' => Str::random(40)]
        );

        $subscriber->subscribed = ! $subscriber->subscribed;
        $subscriber->save();

        return back()->with(
            'success',
            $subscriber->subscribed ? 'Newsletter diaktifkan! 📬' : 'Newsletter dinonaktifkan.'
        );
    }
}
