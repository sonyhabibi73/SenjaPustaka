@extends('layouts.admin')

@section('title', 'Detail Log Aktivitas')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="scroll-text" aria-hidden="true"></i> Detail Log Aktivitas</h1>
    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn--ghost btn--sm">← Kembali ke log</a>
</div>

<div class="admin-card">
    <div class="admin-detail-grid">
        <div class="admin-detail-item">
            <span class="admin-detail-item__label">Waktu</span>
            <span class="admin-detail-item__value mono">{{ $log->created_at->format('d M Y H:i:s') }}</span>
        </div>
        <div class="admin-detail-item">
            <span class="admin-detail-item__label">Pengguna</span>
            <span class="admin-detail-item__value">
                @if ($log->user)
                    {{ $log->user->name }} <small class="text-muted">&lt;{{ $log->user->email }}&gt;</small>
                @else
                    Guest
                @endif
            </span>
        </div>
        <div class="admin-detail-item">
            <span class="admin-detail-item__label">Aksi</span>
            <span class="admin-detail-item__value"><span class="badge-pill">{{ $log->action }}</span></span>
        </div>
        <div class="admin-detail-item">
            <span class="admin-detail-item__label">IP Address</span>
            <span class="admin-detail-item__value mono">{{ $log->ip_address ?? '—' }}</span>
        </div>
        <div class="admin-detail-item admin-detail-item--full">
            <span class="admin-detail-item__label">Deskripsi</span>
            <span class="admin-detail-item__value">{{ $log->description ?? '—' }}</span>
        </div>
        <div class="admin-detail-item admin-detail-item--full">
            <span class="admin-detail-item__label">User Agent</span>
            <span class="admin-detail-item__value mono" style="word-break:break-all;">{{ $log->user_agent ?? '—' }}</span>
        </div>
        @if ($log->metadata)
            <div class="admin-detail-item admin-detail-item--full">
                <span class="admin-detail-item__label">Metadata</span>
                <pre class="admin-detail-json">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>
</div>

@endsection
