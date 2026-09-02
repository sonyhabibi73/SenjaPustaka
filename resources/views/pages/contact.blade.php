@extends('layouts.app')

@section('title', 'Kontak')
@section('page', 'contact')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Hubungi Kami</p>
        <h1><i data-lucide="mail" aria-hidden="true"></i> Kontak</h1>
        <p>Punya pertanyaan, saran, atau ingin bekerja sama? Kami senang mendengarnya.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <div class="reveal">
                <h2 style="font-size:1.6rem;">Kirim Pesan</h2>
                <form method="POST" action="{{ route('contact.send') }}" style="margin-top:var(--sp-5);">
                    @csrf
                    <div class="field">
                        <label for="name">Nama</label>
                        <input type="text" id="name" name="name" class="input" value="{{ old('name', auth()->user()?->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="input" value="{{ old('email', auth()->user()?->email) }}" required>
                    </div>
                    <div class="field">
                        <label for="message">Pesan</label>
                        <textarea id="message" name="message" class="textarea" required placeholder="Tulis pesanmu di sini…"></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary btn--lg"><i data-lucide="send" aria-hidden="true"></i> Kirim Pesan</button>
                </form>
            </div>

            <div class="reveal" style="--d:80ms">
                <h2 style="font-size:1.6rem;">Info Lainnya</h2>
                <div style="margin-top:var(--sp-5);display:grid;gap:var(--sp-4);">
                    @if (config('mail.from.address') && ! str_contains(config('mail.from.address'), 'example.com'))
                        <div class="contact-info-card">
                            <span class="icon-chip"><i data-lucide="mail" aria-hidden="true"></i></span>
                            <div>
                                <strong>Email</strong>
                                <p class="small text-muted" style="margin:0;">{{ config('mail.from.address') }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="contact-info-card">
                        <span class="icon-chip"><i data-lucide="send" aria-hidden="true"></i></span>
                        <div>
                            <strong>Cara Tercepat</strong>
                            <p class="small text-muted" style="margin:0;">Isi form di samping — pesanmu langsung masuk ke tim kami dan akan dibalas ke email yang kamu isi.</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <span class="icon-chip"><i data-lucide="star" aria-hidden="true"></i></span>
                        <div>
                            <strong>Butuh bantuan baca?</strong>
                            <p class="small text-muted" style="margin:0;">Lihat panduan cepat di halaman dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
