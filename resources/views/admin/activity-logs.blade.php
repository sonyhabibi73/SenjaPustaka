@extends('layouts.admin')

@section('title', 'Log Aktivitas')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="scroll-text" aria-hidden="true"></i> Log Aktivitas</h1>
    <p class="text-muted" style="margin:0;">Pantau semua aktivitas pengguna dan admin.</p>
</div>

<div class="admin-card">
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="admin-filters">
        <div class="admin-filters__group">
            <label for="action">Aksi</label>
            <select name="action" id="action" class="input">
                <option value="">Semua Aksi</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-filters__group">
            <label for="user_id">ID Pengguna</label>
            <input type="number" name="user_id" id="user_id" class="input" min="1" value="{{ request('user_id') }}">
        </div>
        <div class="admin-filters__group">
            <label for="date_from">Dari Tanggal</label>
            <input type="date" name="date_from" id="date_from" class="input" value="{{ request('date_from') }}">
        </div>
        <div class="admin-filters__group">
            <label for="date_to">Sampai Tanggal</label>
            <input type="date" name="date_to" id="date_to" class="input" value="{{ request('date_to') }}">
        </div>
        <button type="submit" class="btn btn--primary btn--sm">Filter</button>
        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn--ghost btn--sm">Reset</a>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Pengguna</th>
                <th>Aksi</th>
                <th>Deskripsi</th>
                <th>IP Address</th>
                <th style="text-align:right;">Detail</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="mono">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $log->user?->name ?? 'Guest' }}</td>
                    <td><span class="badge-pill">{{ $log->action }}</span></td>
                    <td class="text-muted">{{ Str::limit($log->description ?? '-', 60) }}</td>
                    <td class="mono">{{ $log->ip_address ?? '—' }}</td>
                    <td>
                        <div class="admin-table__actions">
                            <a href="{{ route('admin.activity-logs.show', $log) }}" class="btn btn--ghost btn--sm">Lihat</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-muted" style="text-align:center;padding:var(--sp-12);">Tidak ada log aktivitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $logs->links('pagination.senja') }}

@endsection
