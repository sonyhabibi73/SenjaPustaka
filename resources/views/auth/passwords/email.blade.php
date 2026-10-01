@extends('layouts.auth')

@section('title', 'Lupa Password')
@section('page', 'forgot-password')

@section('form')

<h1>Lupa Password?</h1>
<p class="auth-form__sub">Masukkan email Anda dan kami akan mengirim link untuk reset password.</p>

@if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="email">
    </div>
    <button type="submit" class="btn btn--primary btn--lg btn--block">Kirim Link Reset Password</button>
</form>

<p class="auth-form__switch">
    <a href="{{ route('login') }}">Kembali ke Halaman Masuk</a>
</p>

<p class="auth-form__switch">
    <a href="{{ route('register') }}">Daftar Akun Baru</a>
</p>

@endsection
