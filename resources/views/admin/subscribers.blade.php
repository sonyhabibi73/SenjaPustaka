@extends('layouts.admin')

@section('title', 'Newsletter')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="mail" aria-hidden="true"></i> Newsletter</h1>
</div>

<div class="admin-card" style="padding:var(--sp-4);">
    <form method="GET" action="{{ route('admin.newsletter.index') }}" style="display:flex;gap:var(--sp-3);">
        <input type="search" name="q" class="input" value="{{ request('q') }}" placeholder="Cari email…">
        <button type="submit" class="btn btn--primary">Cari</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Nama</th>
                <th>Bergabung</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subscribers as $subscriber)
                <tr>
                    <td><strong>{{ $subscriber->email }}</strong></td>
                    <td>{{ $subscriber->name ?? '—' }}</td>
                    <td class="small text-muted">{{ $subscriber->created_at->diffForHumans() }}</td>
                    <td>
                        @if ($subscriber->subscribed)
                            <span class="badge-pill" style="background:var(--color-primary);color:#fff;">Aktif</span>
                        @else
                            <span class="badge-pill" style="opacity:.6;">Berhenti</span>
                        @endif
                    </td>
                    <td>
                        <div class="admin-table__actions">
                            <form method="POST" action="{{ route('admin.newsletter.update', $subscriber) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="subscribed" value="{{ $subscriber->subscribed ? '0' : '1' }}">
                                <button type="submit" class="btn btn--ghost">
                                    {{ $subscriber->subscribed ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.newsletter.destroy', $subscriber) }}" onsubmit="return confirm('Hapus subscriber ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">Belum ada subscriber.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $subscribers->links('pagination.senja') }}

@endsection
