@extends('layouts.auth')

@section('title', 'Daftar')
@section('page', 'register')

@section('form')

<h1>Mulai petualanganmu</h1>
<p class="auth-form__sub">Gratis selamanya. Buat akun dan langsung temukan buku pertamamu.</p>

<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="field">
        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" class="input" value="{{ old('name') }}" required autofocus autocomplete="name">
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="input" value="{{ old('email') }}" required autocomplete="email">
    </div>
    <div class="field">
        <label for="password">Kata Sandi</label>
        <input type="password" id="password" name="password" class="input" required minlength="8" autocomplete="new-password" aria-describedby="password-rules">
        <ul class="password-rules" id="password-rules" aria-label="Syarat kata sandi" aria-live="polite" hidden>
            <li class="password-rule" data-valid="false">
                <span class="password-rule__icon" aria-hidden="true">
                    <i class="password-rule__icon--cross" data-lucide="circle-x"></i>
                    <i class="password-rule__icon--check" data-lucide="circle-check"></i>
                </span>
                <span>Minimal 8 karakter</span>
                <span class="sr-only password-rule__status">Belum terpenuhi</span>
            </li>
            <li class="password-rule" data-valid="false">
                <span class="password-rule__icon" aria-hidden="true">
                    <i class="password-rule__icon--cross" data-lucide="circle-x"></i>
                    <i class="password-rule__icon--check" data-lucide="circle-check"></i>
                </span>
                <span>1 huruf besar</span>
                <span class="sr-only password-rule__status">Belum terpenuhi</span>
            </li>
            <li class="password-rule" data-valid="false">
                <span class="password-rule__icon" aria-hidden="true">
                    <i class="password-rule__icon--cross" data-lucide="circle-x"></i>
                    <i class="password-rule__icon--check" data-lucide="circle-check"></i>
                </span>
                <span>1 angka</span>
                <span class="sr-only password-rule__status">Belum terpenuhi</span>
            </li>
            <li class="password-rule" data-valid="false">
                <span class="password-rule__icon" aria-hidden="true">
                    <i class="password-rule__icon--cross" data-lucide="circle-x"></i>
                    <i class="password-rule__icon--check" data-lucide="circle-check"></i>
                </span>
                <span>1 karakter spesial</span>
                <span class="sr-only password-rule__status">Belum terpenuhi</span>
            </li>
        </ul>
    </div>
    <div class="field">
        <label for="password_confirmation">Ulangi Kata Sandi</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="input" required autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn--primary btn--lg btn--block"><i data-lucide="rocket" aria-hidden="true"></i> Buat Akun</button>
</form>

<p class="auth-form__switch">
    Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
</p>

@endsection
