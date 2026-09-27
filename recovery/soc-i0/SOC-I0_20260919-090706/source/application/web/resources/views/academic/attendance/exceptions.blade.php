<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perlu Perhatian Kehadiran</title>
    <style>@include('academic.partials.sidebar-styles') .flag{display:inline-block;margin:.15rem .25rem .15rem 0;padding:.25rem .5rem;border-radius:99px;background:#fff1d6;color:#76500b;font-size:.85rem;font-weight:700}.attendance-table{min-width:56rem}.empty{color:#176b45}.attendance-action{display:inline-block;padding:.5rem .75rem;border-radius:.55rem;background:#176b45;color:#fff;text-decoration:none;font-weight:800;white-space:nowrap}.attendance-action:hover,.attendance-action:focus-visible{background:#0d573a}.bulk-cancel{display:grid;gap:.8rem}.bulk-cancel-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr auto;gap:.7rem;align-items:end}.bulk-cancel-submit{display:grid;gap:.8rem;margin-top:.1rem}.bulk-roster-submit{padding:.75rem;background:#f3faf6;border:1px solid #cfe5d8;border-radius:.65rem;margin:0 0 .85rem}.bulk-roster-submit button{background:#176b45!important;justify-self:start!important;width:auto!important}.bulk-cancel label{display:grid;gap:.3rem;color:#345b4e;font-weight:700;font-size:.8rem}.bulk-date-day{color:#176b45;font-size:.9rem;font-weight:800}.bulk-date-picker{position:relative}.bulk-date-picker .bulk-date-display{color:#17352b;background:#fff;padding-right:3rem}.bulk-date-picker .bulk-date-native{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.bulk-date-trigger{position:absolute;right:.6rem;top:50%;transform:translateY(-50%);z-index:2;width:auto!important;margin:0;padding:.35rem!important;border:0!important;background:transparent!important;color:#176b45!important;cursor:pointer}.bulk-date-trigger::before{content:"";display:block;width:1rem;height:.85rem;border:2px solid currentColor;border-radius:.18rem;background:linear-gradient(to bottom,currentColor 0 .2rem,transparent .2rem)}.bulk-cancel input,.bulk-cancel select,.bulk-cancel textarea{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.65rem;font:inherit;background:#fff}.bulk-cancel textarea{min-height:3.8rem;resize:vertical}.bulk-cancel button{border:0;border-radius:.55rem;padding:.65rem .9rem;font:inherit;font-weight:800;cursor:pointer;background:#a94b1b;color:#fff;justify-self:start}.bulk-preview{border:1px solid #efd7bd;border-radius:.65rem;background:#fffaf4;padding:.7rem;color:#76500b}.bulk-preview strong{color:#a94b1b}@media(max-width:760px){.bulk-cancel-grid{grid-template-columns:1fr}.bulk-cancel button{width:100%}.bulk-roster-submit button{width:auto!important}}</style>
</style><style>.exception-list-filter{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.7rem;align-items:end;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #e2eee7}.exception-list-filter>strong{grid-column:1/-1;color:#17352b}.exception-list-filter label{display:grid;gap:.3rem;margin:0;color:#345b4e;font-size:.8rem}.exception-list-filter input,.exception-list-filter select{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.6rem;font:inherit;background:#fff}.filter-submit{border:0;border-radius:.55rem;padding:.65rem .9rem;background:#176b45;color:#fff;font:inherit;font-weight:800;cursor:pointer}@media(max-width:760px){.exception-list-filter{grid-template-columns:1fr 1fr}.exception-list-filter>strong{grid-column:1/-1}.filter-submit{width:100%}}</style>
<style>
    .exception-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:1.5rem;padding:1.35rem 1.5rem;background:linear-gradient(135deg,#f2faf6,#fff);border:1px solid #d7e9df;border-radius:1rem}.exception-hero h1{margin:.2rem 0 .45rem}.exception-hero .muted{max-width:42rem;margin:0}.exception-hero-count{flex:0 0 auto;min-width:9rem;padding:.85rem 1rem;border:1px solid #bfe0cd;border-radius:.8rem;background:#fff;text-align:center;color:#176b45}.exception-hero-count strong{display:block;font-size:1.9rem;line-height:1.05}.exception-hero-count span{display:block;margin-top:.25rem;font-size:.78rem;font-weight:800}.exception-feedback{display:flex;align-items:flex-start;gap:.7rem;margin-top:.9rem;padding:.8rem 1rem;border:1px solid #bfe0cd;border-radius:.7rem;background:#edf9f1;color:#176b45}.exception-feedback.error{border-color:#edc5b8;background:#fff5f1;color:#9a3f22}.exception-feedback strong{display:block;margin-bottom:.2rem}.exception-feedback ul{margin:.2rem 0 0 1rem;padding:0}.exception-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem;margin:.9rem 0 1rem}.exception-summary-tile{position:relative;min-height:6.5rem;padding:1rem 1.1rem;border:1px solid #d7e9df;border-radius:.8rem;background:#fff;overflow:hidden}.exception-summary-tile::before{content:"";position:absolute;inset:0 auto 0 0;width:.25rem;background:#176b45}.exception-summary-tile.amber::before{background:#c6891b}.exception-summary-tile.blue::before{background:#557aa0}.exception-summary-tile strong{display:block;font-size:1.8rem;line-height:1;color:#17352b}.exception-summary-tile span{display:block;margin-top:.45rem;color:#345b4e;font-weight:800}.exception-summary-tile small{display:block;margin-top:.25rem;color:#70877e;font-size:.76rem;line-height:1.25}@media(max-width:900px){.exception-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.exception-hero{align-items:stretch;flex-direction:column;padding:1.1rem}.exception-hero-count{min-width:0;text-align:left}.exception-hero-count strong,.exception-hero-count span{display:inline}.exception-hero-count span{margin-left:.35rem}.exception-summary{grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}.exception-summary-tile{min-height:5.8rem;padding:.85rem}.exception-summary-tile strong{font-size:1.5rem}.exception-summary-tile span{font-size:.85rem}}
    .exception-filter-card{padding:1rem 1.1rem}.exception-filter-bar{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.7rem;align-items:end}.exception-filter-bar>strong{grid-column:1/-1;color:#17352b;letter-spacing:.02em}.exception-filter-bar label{display:grid;gap:.3rem;margin:0;color:#345b4e;font-size:.8rem}.exception-filter-bar input,.exception-filter-bar select{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.6rem;font:inherit;background:#fff}.exception-filter-bar .filter-submit{border:0;border-radius:.55rem;padding:.65rem .9rem;background:#176b45;color:#fff;font:inherit;font-weight:800;cursor:pointer}.exception-filter-context{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-top:.85rem;padding-top:.8rem;border-top:1px solid #e2eee7}.exception-filter-context p{margin:0;color:#61776d;font-size:.9rem}.exception-filter-context strong{color:#17352b}.exception-filter-tags{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.35rem}.exception-filter-tag{display:inline-flex;align-items:center;padding:.25rem .55rem;border:1px solid #cfe5d8;border-radius:99px;background:#f3faf6;color:#176b45;font-size:.78rem;font-weight:800}.exception-filter-reset{color:#176b45;font-size:.85rem;font-weight:800;text-decoration:none;white-space:nowrap}.exception-filter-reset:hover,.exception-filter-reset:focus-visible{text-decoration:underline}@media(max-width:900px){.exception-filter-bar{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.exception-filter-card{padding:.9rem}.exception-filter-bar{grid-template-columns:1fr}.exception-filter-context{align-items:flex-start;flex-direction:column;gap:.55rem}.exception-filter-reset{align-self:flex-start}}
</style>
<style>
    .exception-queue-wrap{overflow:visible}.exception-queue{width:100%;min-width:0;border-collapse:collapse}.exception-queue th,.exception-queue td{padding:.8rem .65rem;text-align:left;vertical-align:middle}.exception-queue tbody tr{transition:background-color .15s ease}.exception-queue tbody tr:hover,.exception-queue tbody tr:focus-within{background:#f7fbf8}.exception-queue .attendance-action{display:inline-flex;align-items:center;justify-content:center;min-height:2.5rem;padding:.55rem .8rem;border:1px solid #b9dac7;border-radius:.6rem;background:#edf8f1;color:#176b4d;font-weight:800;text-decoration:none;white-space:nowrap}.exception-queue .attendance-action:hover,.exception-queue .attendance-action:focus-visible{background:#dff2e7;color:#0d573a}.exception-queue form .attendance-action{border-color:#176b4d;background:#176b4d;color:#fff}.exception-queue form .attendance-action:hover,.exception-queue form .attendance-action:focus-visible{background:#0d573a;color:#fff}
    .bulk-action-workspace{margin:0 0 1rem;border:1px solid #d7e9df;border-radius:.85rem;background:#fbfefc;overflow:hidden}.bulk-action-workspace>summary{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1rem;color:#17352b;font-weight:800;cursor:pointer;list-style:none}.bulk-action-workspace>summary::-webkit-details-marker{display:none}.bulk-action-workspace>summary::after{content:'▾';color:#176b45;font-size:1.1rem;transition:transform .15s ease}.bulk-action-workspace[open]>summary{border-bottom:1px solid #d7e9df;background:#f3faf6}.bulk-action-workspace[open]>summary::after{transform:rotate(180deg)}.bulk-action-workspace>summary small{display:block;margin-top:.2rem;color:#70877e;font-size:.78rem;font-weight:600}.bulk-action-content{padding:1rem}.bulk-action-content h3{margin:.05rem 0 .25rem;font-size:1rem}.bulk-action-content>p{margin:0 0 .9rem;color:#61776d}.bulk-preview{display:grid;gap:.7rem;border:1px solid #d7e9df;border-radius:.7rem;background:#fff;padding:.85rem;color:#345b4e}.bulk-preview strong{color:#176b45;font-size:1rem}.bulk-preview-empty{border-color:#d7e9df;background:#f7fbf8;color:#61776d}.bulk-preview-list{border:1px solid #e2eee7;border-radius:.55rem;background:#fbfefc}.bulk-preview-list summary{padding:.55rem .7rem;color:#176b45;font-weight:800;cursor:pointer}.bulk-preview-items{display:grid;gap:.35rem;padding:0 .7rem .7rem}.bulk-preview-item{display:flex;justify-content:space-between;gap:1rem;padding:.45rem 0;border-top:1px solid #e8f1ec}.bulk-preview-item strong{font-size:.86rem;color:#17352b}.bulk-preview-item span{color:#61776d;font-size:.84rem;white-space:nowrap}.bulk-cancel .bulk-action-preview{background:#176b45!important;color:#fff}.bulk-cancel .bulk-submit{background:#a94b1b}.bulk-cancel .bulk-submit:hover,.bulk-cancel .bulk-submit:focus-visible{background:#873a16}.bulk-safety-note{margin:0;color:#70877e;font-size:.82rem;line-height:1.4}@media(max-width:760px){.bulk-action-workspace>summary{align-items:flex-start}.bulk-action-content{padding:.85rem}.bulk-preview-item{align-items:flex-start;flex-direction:column;gap:.15rem}.bulk-preview-item span{white-space:normal}.bulk-cancel .bulk-submit{width:100%}}
    @media(max-width:760px){.exception-queue-wrap{overflow:visible}.exception-queue,.exception-queue thead,.exception-queue tbody,.exception-queue tr,.exception-queue td{display:block;width:100%;min-width:0;box-sizing:border-box}.exception-queue thead{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);clip-path:inset(50%);white-space:nowrap}.exception-queue tbody{display:grid;gap:.7rem}.exception-queue tbody tr{padding:.8rem;border:1px solid #d7e9df;border-radius:.8rem;background:#fff}.exception-queue td{border:0;padding:.25rem 0}.exception-queue td::before{display:block;margin-bottom:.15rem;color:#70877e;content:attr(data-label);font-size:.7rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.exception-queue td:first-child{padding-top:0}.exception-queue td:first-child::before{display:none}.exception-queue td:last-child{padding-bottom:0}.exception-queue td:last-child .attendance-action{width:100%}.exception-queue .flag{margin-top:.2rem}}
</style>
<style>
    .bulk-action-workspace>summary:hover{background:#f3faf6}.bulk-action-workspace>summary:focus-visible{outline:3px solid #8bc9a6;outline-offset:-3px}.bulk-date-picker:focus-within{outline:3px solid #8bc9a6;outline-offset:2px}.bulk-date-picker .bulk-date-native:focus{outline:0}
</style>
<style>.exception-joint-label{color:#176b45;font-weight:700}</style>
<style>
    .bulk-action-workspace{margin-top:1rem!important;margin-bottom:1rem!important}
</style>
</head>
<body>
@php($weekdayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'])
@php($totalExceptions = $exceptions->count())
@php($missingRosterSessions = $exceptions->filter(fn (array $item): bool => $item['finding']['no_participants'] ?? false)->count())
@php($missingAttendanceSessions = $exceptions->filter(fn (array $item): bool => ($item['finding']['missing_attendance_participant_ids'] ?? []) !== [])->count())
@php($unresolvedAttendanceSessions = $exceptions->filter(fn (array $item): bool => ($item['finding']['unresolved_attendance_participant_ids'] ?? []) !== [])->count())
@php($bulkActionOpen = ($filters['from'] ?? null) && ($filters['to'] ?? null) || $errors->any() || old('class_id') !== null || old('from') !== null || old('to') !== null || old('reason') !== null)
@php($monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'])
<div class="waka-shell">@include('academic.partials.sidebar', ['activeMenu' => 'attendance'])<main class="waka-content"><header class="exception-hero"><div><p class="eyebrow">Kontrol kualitas data</p><h1>Perlu Perhatian Kehadiran</h1><p class="muted">Sesi dengan roster atau data kehadiran yang masih perlu dituntaskan.</p></div><div class="exception-hero-count" data-exception-summary="total" data-count="{{ $totalExceptions }}"><strong>{{ $totalExceptions }}</strong><span>Perlu perhatian</span></div></header>
    @if (session('status'))
        <div class="exception-feedback" role="status" aria-live="polite"><span aria-hidden="true">✓</span><div>{{ session('status') }}</div></div>
    @endif
    @if ($errors->any())
        <div class="exception-feedback error" role="alert"><div><strong>Periksa kembali input berikut:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif
    <section class="exception-summary" aria-label="Ringkasan sesi yang perlu perhatian">
        <div class="exception-summary-tile" data-exception-summary="total" data-count="{{ $totalExceptions }}"><strong>{{ $totalExceptions }}</strong><span>Perlu perhatian</span><small>Jumlah sesi pada daftar saat ini</small></div>
        <div class="exception-summary-tile amber" data-exception-summary="missing-roster" data-count="{{ $missingRosterSessions }}"><strong>{{ $missingRosterSessions }}</strong><span>Roster belum dibuat</span><small>Sesi tanpa roster santri</small></div>
        <div class="exception-summary-tile amber" data-exception-summary="missing-attendance" data-count="{{ $missingAttendanceSessions }}"><strong>{{ $missingAttendanceSessions }}</strong><span>Belum diisi</span><small>Sesi dengan data kehadiran belum lengkap</small></div>
        <div class="exception-summary-tile blue" data-exception-summary="unresolved-attendance" data-count="{{ $unresolvedAttendanceSessions }}"><strong>{{ $unresolvedAttendanceSessions }}</strong><span>Belum tervalidasi</span><small>Sesi dengan data kehadiran belum tervalidasi</small></div>
    </section>
    <section class="card exception-filter-card">
        <form class="exception-filter-bar" method="GET" action="{{ route('academic.attendance.exceptions') }}">
            <strong>Filter temuan</strong>
            <label>Bulan<input type="month" name="list_month" value="{{ $listFilters['list_month'] ?? '' }}"></label>
            <label>Kelas<select name="list_class_id"><option value="">Semua kelas resmi</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected(($listFilters['list_class_id'] ?? '') === $class->id)>{{ $class->display_name }}</option>@endforeach</select></label>
            <label>Urutan<select name="list_sort"><option value="newest" @selected(($listFilters['list_sort'] ?? 'newest') === 'newest')>Terbaru dahulu</option><option value="oldest" @selected(($listFilters['list_sort'] ?? '') === 'oldest')>Terlama dahulu</option></select></label>
            <button class="filter-submit" type="submit">Terapkan</button>
        </form>
        <div class="exception-filter-context"><div><p><strong>{{ $totalExceptions }} sesi ditemukan</strong></p><div class="exception-filter-tags" aria-label="Filter aktif">@if (!empty($listFilters['list_month']))<span class="exception-filter-tag">{{ $monthNames[(int) substr($listFilters['list_month'], 5, 2)] ?? $listFilters['list_month'] }} {{ substr($listFilters['list_month'], 0, 4) }}</span>@endif @if (!empty($listFilters['list_class_id']))<span class="exception-filter-tag">{{ $classes->firstWhere('id', $listFilters['list_class_id'])?->display_name ?? 'Kelas terpilih' }}</span>@endif @if (!empty($listFilters['list_sort']))<span class="exception-filter-tag">{{ $listFilters['list_sort'] === 'oldest' ? 'Terlama dahulu' : 'Terbaru dahulu' }}</span>@endif</div></div><a class="exception-filter-reset" href="{{ route('academic.attendance.exceptions') }}">Reset filter</a></div>
        <details id="bulk-cancel" class="bulk-action-workspace bulk-cancel" @if ($bulkActionOpen) open @endif>
            <summary><span>Aksi massal<small>Kelola tindakan terhadap beberapa sesi</small></span></summary>
            <div class="bulk-action-content">
                <h3>Pembatalan sesi</h3>
                <p>Batalkan beberapa sesi yang masih aman dibatalkan.</p>
                <form class="bulk-cancel-grid" method="GET" action="{{ route('academic.attendance.exceptions') }}#bulk-cancel">
                    <label>Kelas<select name="class_id"><option value="">Semua kelas resmi</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(($bulkFormFilters['class_id'] ?? '') === $class->id)>{{ $class->display_name }}</option>@endforeach</select></label>
                    <label>Mulai <span class="bulk-date-day" data-date-day="from">{{ !empty($bulkFormFilters['from']) ? ($weekdayNames[\Illuminate\Support\Carbon::parse($bulkFormFilters['from'])->format('l')] ?? '') : '' }}</span><div class="bulk-date-picker"><input class="bulk-date-display" type="text" value="{{ !empty($bulkFormFilters['from']) ? \Illuminate\Support\Carbon::parse($bulkFormFilters['from'])->format('d/m/Y') : '' }}" placeholder="dd/mm/yyyy" readonly aria-hidden="true"><button class="bulk-date-trigger" type="button" aria-label="Buka kalender tanggal mulai"></button><input class="bulk-date-native" lang="id" type="date" name="from" value="{{ $bulkFormFilters['from'] ?? '' }}" required aria-label="Tanggal mulai"></div></label>
                    <label>Sampai <span class="bulk-date-day" data-date-day="to">{{ !empty($bulkFormFilters['to']) ? ($weekdayNames[\Illuminate\Support\Carbon::parse($bulkFormFilters['to'])->format('l')] ?? '') : '' }}</span><div class="bulk-date-picker"><input class="bulk-date-display" type="text" value="{{ !empty($bulkFormFilters['to']) ? \Illuminate\Support\Carbon::parse($bulkFormFilters['to'])->format('d/m/Y') : '' }}" placeholder="dd/mm/yyyy" readonly aria-hidden="true"><button class="bulk-date-trigger" type="button" aria-label="Buka kalender tanggal selesai"></button><input class="bulk-date-native" lang="id" type="date" name="to" value="{{ $bulkFormFilters['to'] ?? '' }}" required aria-label="Tanggal selesai"></div></label>
                    <button class="bulk-action-preview" type="submit">Pratinjau</button>
                </form>
                @if (($filters['from'] ?? null) && ($filters['to'] ?? null))
                    @if ($bulkSessions->isNotEmpty())
                        <div class="bulk-preview"><strong>{{ $bulkSessions->count() }} sesi memenuhi syarat pembatalan</strong><span>Hanya sesi yang masih memenuhi aturan sistem yang akan dibatalkan.</span><details class="bulk-preview-list" @if ($bulkSessions->count() <= 6) open @endif><summary>Lihat {{ $bulkSessions->count() }} sesi</summary><div class="bulk-preview-items">@foreach ($bulkSessions as $bulkSession)@php($bulkScopeClasses = $bulkSession->scopeGroups->pluck('academicClass')->filter()->sortBy('display_name')->values())@php($bulkScopeLabel = $bulkScopeClasses->count() > 1 ? $bulkScopeClasses->pluck('display_name')->implode(' + ') : ($bulkScopeClasses->first()?->display_name ?? $bulkSession->academicClass?->display_name ?? $bulkSession->class_id))<div class="bulk-preview-item"><strong>{{ $bulkScopeLabel }}</strong>@if ($bulkScopeClasses->count() > 1)<small class="exception-joint-label">Kelas gabungan</small>@endif<span>{{ $weekdayNames[$bulkSession->planned_start_at->format('l')] ?? $bulkSession->planned_start_at->format('l') }}, {{ $bulkSession->planned_start_at->format('d M Y · H:i') }}</span></div>@endforeach</div></details></div>
                        <p class="bulk-safety-note">Periksa daftar sesi sebelum melanjutkan. Pembatalan akan tercatat dalam riwayat perubahan.</p>
                        <form class="bulk-cancel-submit" method="POST" action="{{ route('academic.attendance.exceptions.bulk-cancel') }}" onsubmit="return window.confirm('Batalkan {{ $bulkSessions->count() }} sesi yang tampil dalam pratinjau?')">
                            @csrf<input type="hidden" name="class_id" value="{{ $filters['class_id'] ?? '' }}"><input type="hidden" name="from" value="{{ $filters['from'] }}"><input type="hidden" name="to" value="{{ $filters['to'] }}">
                            <label>Alasan pembatalan (wajib diisi)<textarea name="reason" required maxlength="1000" placeholder="Contoh: KBM baru dimulai 7 Juli 2026">{{ old('reason') }}</textarea></label>
                            <button class="bulk-submit" type="submit">Batalkan {{ $bulkSessions->count() }} sesi</button>
                        </form>
                    @else
                        <div class="bulk-preview bulk-preview-empty"><strong>Tidak ada sesi yang aman dibatalkan untuk rentang yang dipilih.</strong><span>Periksa kembali kelas dan tanggal, atau lanjutkan tanpa pembatalan.</span></div>
                    @endif
                @endif
            </div>
        </details>
        @if ($exceptions->isEmpty())
            <div style="text-align:center;padding:1.5rem .75rem"><div style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:99px;background:#e7f4ec;color:#176b4d;font-size:1.35rem;font-weight:800">✓</div><p class="empty" style="font-weight:800;margin:.7rem 0 .25rem">Semua data kehadiran rapi</p><p class="muted" style="margin:0">Tidak ada sesi yang perlu diperiksa saat ini.</p></div>
        @else
            @php($rosterItems = $exceptions->filter(fn (array $item): bool => $item['finding']['no_participants'] ?? false))
            @if ($rosterItems->isNotEmpty())
                <form class="bulk-cancel-submit bulk-roster-submit" method="POST" action="{{ route('academic.attendance.exceptions.bulk-snapshot') }}" onsubmit="return window.confirm('Buat roster untuk {{ $rosterItems->count() }} sesi kosong?')">
                    @csrf
                    @foreach ($rosterItems as $rosterItem)<input type="hidden" name="session_ids[]" value="{{ $rosterItem['session']->id }}">@endforeach
                    <input type="hidden" name="list_month" value="{{ $listFilters['list_month'] ?? '' }}"><input type="hidden" name="list_class_id" value="{{ $listFilters['list_class_id'] ?? '' }}"><input type="hidden" name="list_sort" value="{{ $listFilters['list_sort'] ?? '' }}">
                    <button type="submit" style="background:#176b45">Buat roster untuk {{ $rosterItems->count() }} sesi kosong</button>
                </form>
            @endif
            <div class="table-wrap exception-queue-wrap">
                <table class="exception-queue">
                    <thead><tr><th>Sesi</th><th>Waktu</th><th>Temuan</th><th>Aksi</th></tr></thead>
                    <tbody>
                    @foreach ($exceptions as $item)
                        @php($session = $item['session'])
                        @php($finding = $item['finding'])
                        <tr>
                            @php($scopeClasses = $session->scopeGroups->pluck('academicClass')->filter()->sortBy('display_name')->values())
                            @php($scopeLabel = $scopeClasses->count() > 1 ? $scopeClasses->pluck('display_name')->implode(' + ') : ($scopeClasses->first()?->display_name ?? $session->academicClass?->display_name ?? 'Kelas'))
                            <td data-label="Sesi"><strong>{{ $scopeLabel }}</strong>@if ($scopeClasses->count() > 1)<br><small class="exception-joint-label">Kelas gabungan</small>@endif<br><span class="muted">{{ ['PLANNED' => 'Dijadwalkan', 'CONFIRMED' => 'Dikonfirmasi', 'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan'][$session->session_status] ?? $session->session_status }}</span></td>
                            <td data-label="Waktu"><strong>{{ $weekdayNames[$session->planned_start_at->format('l')] ?? $session->planned_start_at->format('l') }}</strong><br><span class="muted">{{ $session->planned_start_at->format('d M Y, H:i') }}</span></td>
                            <td data-label="Temuan">
                                @if ($finding['no_participants'] ?? false)
                                    <span class="flag">Roster santri belum dibuat</span>
                                @endif
                                @if ($finding['missing_attendance_participant_ids'] !== [])
                                    <span class="flag">{{ count($finding['missing_attendance_participant_ids']) }} belum diisi</span>
                                @endif
                                @if ($finding['unresolved_attendance_participant_ids'] !== [])
                                    <span class="flag">{{ count($finding['unresolved_attendance_participant_ids']) }} belum tervalidasi</span>
                                @endif
                            </td>
                            <td data-label="Aksi">@if ($finding['no_participants'] ?? false)<form method="POST" action="{{ route('academic.attendance.snapshot-participants', $session) }}" onsubmit="return window.confirm('Buat snapshot roster santri untuk sesi ini?')">@csrf<button class="attendance-action" type="submit">Buat roster</button></form>@else<a class="attendance-action" href="{{ route('academic.attendance.show', $session) }}">Isi kehadiran →</a>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main></div>
<script>
    const weekdayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    document.querySelectorAll('input.bulk-date-native').forEach(function (input) {
        const display = input.parentElement.querySelector('.bulk-date-display');
        const trigger = input.parentElement.querySelector('.bulk-date-trigger');
        const day = document.querySelector('[data-date-day="' + input.name + '"]');
        const openPicker = function () {
            if (typeof input.showPicker === 'function') input.showPicker();
            else input.click();
        };
        const updateDay = function () {
            if (display) display.value = input.value ? input.value.split('-').reverse().join('/') : '';
            if (!day || !input.value) {
                if (day) day.textContent = '';
                return;
            }
            const parts = input.value.split('-').map(Number);
            day.textContent = weekdayNames[new Date(parts[0], parts[1] - 1, parts[2]).getDay()];
        };
        input.addEventListener('input', updateDay);
        input.addEventListener('change', updateDay);
        if (trigger) trigger.addEventListener('click', openPicker);
        if (display) display.addEventListener('click', openPicker);
        updateDay();
    });
</script>
</body>
</html>
