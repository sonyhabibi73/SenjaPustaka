@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="users" aria-hidden="true"></i> Pengguna</h1>
</div>

<div class="admin-card" style="padding:var(--sp-4);">
    <form method="GET" action="{{ route('admin.user.index') }}" style="display:flex;gap:var(--sp-3);">
        <input type="search" name="q" class="input" value="{{ request('q') }}" placeholder="Cari nama atau email…">
        <button type="submit" class="btn btn--primary">Cari</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Poin</th>
                <th>Streak</th>
                <th>Buku</th>
                <th>Peran</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="avatar" style="width:34px;height:34px;font-size:.72rem;">{{ $user->initials() }}</span>
                            <div>
                                <strong style="display:block;font-size:.9rem;">{{ $user->name }}</strong>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="mono">{{ number_format($user->points) }}</td>
                    <td class="mono"><i data-lucide="flame" aria-hidden="true"></i> {{ $user->streak_days }}</td>
                    <td class="mono">{{ $user->progress_count }}</td>
                    <td>
                        @if ($user->is_admin)
                            <span class="badge-pill" style="background:var(--color-primary);color:#fff;">Admin</span>
                        @else
                            <span class="badge-pill">Member</span>
                        @endif
                    </td>
                    <td>
                        <div class="admin-table__actions">
                            <form method="POST" action="{{ route('admin.user.update', $user) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_admin" value="{{ $user->is_admin ? '0' : '1' }}">
                                <button type="submit" class="btn btn--ghost">
                                    {{ $user->is_admin ? 'Turunkan' : 'Jadikan Admin' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.user.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">Belum ada pengguna.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $users->links('pagination.senja') }}

@endsection
