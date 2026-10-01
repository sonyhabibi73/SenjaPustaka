@extends('layouts.auth')

@section('title', 'Masuk')
@section('page', 'login')

@section('form')

<h1>Selamat datang kembali</h1>
<p class="auth-form__sub">Masuk untuk melanjutkan cerita terakhirmu.</p>

<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="email">
    </div>
    <div class="field">
        <label for="password">Kata Sandi</label>
        <input type="password" id="password" name="password" class="input" required autocomplete="current-password">
    </div>
    <div class="field field--row">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Ingat saya</label>
        <a href="{{ route('password.request') }}" class="field__link">Lupa kata sandi?</a>
    </div>
    <button type="submit" class="btn btn--primary btn--lg btn--block"><i data-lucide="log-in" aria-hidden="true"></i> Masuk</button>
</form>

<p class="auth-form__switch">
    Belum punya akun? <a href="{{ route('register') }}">Daftar gratis</a>
</p>

@endsection
