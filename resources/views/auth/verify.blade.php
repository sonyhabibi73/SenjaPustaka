@extends('layouts.auth')

@section('title', 'Verifikasi Email')
@section('page', 'verify')

@section('form')

<h1>Verifikasi Email Anda</h1>
<p class="auth-form__sub">Kami telah mengirim link verifikasi ke email Anda. Silakan cek inbox untuk melanjutkan.</p>

@if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<div class="verify-resend">
    <p>Tidak menerima email? Klik tombol di bawah untuk mengirim ulang.</p>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn--primary btn--lg btn--block">Kirim Ulang Email Verifikasi</button>
    </form>
</div>

<p class="auth-form__switch">
    <a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
</p>

<p class="auth-form__switch">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="link-btn">Keluar</button>
    </form>
</p>

@endsection
