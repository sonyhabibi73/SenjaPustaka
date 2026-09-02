@extends('layouts.app')

@section('title', 'Profil')
@section('page', 'profile')

@section('content')

<section class="section">
    <div class="container" style="max-width:860px;">
        <div class="section-head reveal">
            <div>
                <p class="eyebrow">Akun Kamu</p>
                <h2><i data-lucide="user" aria-hidden="true"></i> Profil</h2>
            </div>
        </div>

        <div class="profile-card reveal">
            <div class="profile-card__head">
                @include('partials.avatar', ['user' => auth()->user(), 'class' => 'avatar--lg avatar--ring'])
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->email }} · <span class="mono">{{ number_format(auth()->user()->points) }}</span> poin <i data-lucide="star" aria-hidden="true"></i></span>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="input" value="{{ old('name', auth()->user()->name) }}" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="input" value="{{ old('email', auth()->user()->email) }}" required>
                </div>

                <div class="field">
                    <label for="bio">Bio (tentangmu)</label>
                    <textarea id="bio" name="bio" class="textarea" style="min-height:90px;" placeholder="Ceritakan sedikit tentang dirimu…">{{ old('bio', auth()->user()->bio) }}</textarea>
                </div>

                <div class="field">
                    <label for="avatar">Foto Profil</label>
                    <input type="file" id="avatar" name="avatar" class="input" accept="image/*">
                    <small class="text-muted">JPG, PNG, atau WebP maksimal 2MB.</small>
                </div>

                <hr style="border:0;border-top:1px solid var(--color-border);margin:var(--sp-6) 0;">

                <p class="eyebrow">Ganti Kata Sandi <span class="text-muted" style="text-transform:none;letter-spacing:0;">(opsional)</span></p>

                <div class="field">
                    <label for="current_password">Kata Sandi Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" class="input" autocomplete="current-password">
                </div>

                <div class="admin-card__grid">
                    <div class="field" style="margin:0;">
                        <label for="new_password">Kata Sandi Baru</label>
                        <input type="password" id="new_password" name="new_password" class="input" autocomplete="new-password">
                    </div>
                    <div class="field" style="margin:0;">
                        <label for="new_password_confirmation">Ulangi Kata Sandi Baru</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="input" autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn btn--primary btn--lg" style="margin-top:var(--sp-6);"><i data-lucide="save" aria-hidden="true"></i> Simpan Perubahan</button>
            </form>

            <hr style="border:0;border-top:1px solid var(--color-border);margin:var(--sp-6) 0;">

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn--ghost"><i data-lucide="log-out" aria-hidden="true"></i> Keluar dari akun</button>
            </form>
        </div>
    </div>
</section>

@endsection
