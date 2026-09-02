@extends('layouts.auth')

@section('title', 'Daftar')
@section('page', 'register')

@section('form')

<h1>Mulai petualanganmu ✨</h1>
<p class="auth-form__sub">Gratis selamanya — buat akun dan langsung temukan buku pertamamu.</p>

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
        <input type="password" id="password" name="password" class="input" required minlength="8" autocomplete="new-password">
        <small class="text-muted">Minimal 8 karakter.</small>
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
