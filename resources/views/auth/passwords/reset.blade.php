@extends('layouts.auth')

@section('title', 'Reset Password')
@section('page', 'reset-password')

@section('form')

<h1>Reset Password</h1>
<p class="auth-form__sub">Masukkan password baru untuk akun Anda.</p>

@if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="input" value="{{ $email ?? old('email') }}" required autofocus autocomplete="email">
    </div>

    <div class="field">
        <label for="password">Password Baru</label>
        <input type="password" id="password" name="password" class="input" required minlength="12" autocomplete="new-password">
        <small class="text-muted">Minimal 12 karakter, harus mengandung huruf besar, huruf kecil, angka, dan simbol.</small>
    </div>

    <div class="field">
        <label for="password_confirmation">Ulangi Password Baru</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="input" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn--primary btn--lg btn--block">Reset Password</button>
</form>

<p class="auth-form__switch">
    <a href="{{ route('login') }}">Kembali ke Halaman Masuk</a>
</p>

<p class="auth-form__switch">
    <a href="{{ route('register') }}">Daftar Akun Baru</a>
</p>

@endsection
