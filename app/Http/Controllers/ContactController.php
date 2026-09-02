<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Mail::raw(
            "Pesan baru dari halaman Kontak SenjaPustaka\n\n".
            "Dari: {$data['name']} <{$data['email']}>\n\n".
            $data['message'],
            function ($message) {
                $message->to(config('mail.from.address'))
                    ->subject('Pesan baru dari halaman Kontak');
            }
        );

        return back()->with('success', 'Pesan terkirim! Kami akan segera membalas.');
    }
}
