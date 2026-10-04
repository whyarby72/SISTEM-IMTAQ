<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesi Selesai — Pemeriksaan Akademik</title>
    <style>
        @include('academic.partials.sidebar-styles')
        .filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(12rem,1fr));gap:.8rem;align-items:end}
        .filters label{display:grid;gap:.35rem;font-weight:700;color:#31584a}
        .filters input,.filters select{border:1px solid #bfd3c7;border-radius:.55rem;padding:.7rem;font:inherit;background:#fff}
        .session-list{display:grid;gap:.8rem}
        .session-item{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem;border:1px solid #dce8e1;border-radius:.75rem;background:#f8fcfa}
        .session-item div{display:grid;gap:.25rem}
        .session-item small{color:#617970}.session-item .review-joint-label{color:#176b4d;font-weight:800}
        .session-item a{color:#176b4d;font-weight:700;white-space:nowrap}
        @media(max-width:640px){.session-item{align-items:flex-start;flex-direction:column}.session-item a{white-space:normal}}
    </style>
</head>
<body>
<div class="waka-shell">@include('academic.partials.sidebar', ['activeMenu' => 'attendance'])<main class="waka-content"><header class="waka-topbar"><div><p class="eyebrow">Pemeriksaan Waka Akademik</p><h1>Sesi Kehadiran yang Sudah Disahkan</h1></div></header>
    <header class="card">
        <p class="eyebrow">Pemeriksaan Waka Akademik</p>
        <h1>Sesi Kehadiran yang Sudah Disahkan</h1>
        <p class="muted">Tinjau hasil kehadiran santri tanpa mengubah data.</p>
    </header>
    <section class="card">
        <form class="filters" method="GET" action="{{ route('academic.attendance.reviews') }}">
            <label>Kelas
                <select name="class_id">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((string) ($filters['class_id'] ?? '') === (string) $class->id)>{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Dari tanggal<input lang="id" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
            <label>Sampai tanggal<input lang="id" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
            <button class="button" type="submit">Tampilkan</button>
        </form>
        <div style="display:flex;flex-wrap:wrap;gap:.7rem;margin-top:1rem">
            <a class="button" href="{{ route('academic.attendance.reviews.csv', request()->query()) }}">Unduh CSV</a>
            <a class="button" href="{{ route('academic.attendance.reviews.pdf', request()->query()) }}">Unduh PDF</a>
        </div>
    </section>
    <section class="card">
        <div class="section-heading"><div><p class="eyebrow">Hasil pemeriksaan</p><h2>{{ $sessions->total() }} sesi selesai</h2></div></div>
        @if ($sessions->isEmpty())
            <p class="muted">Belum ada sesi selesai yang sesuai dengan filter.</p>
        @else
            <div class="session-list">
                @foreach ($sessions as $session)
                    @php($summary = $session->studentParticipants->map(fn ($participant) => $participant->attendance?->attendance_status ?? 'PENDING')->countBy())
                    <div class="session-item">
                        @php($scopeClasses = $session->scopeGroups->pluck('academicClass')->filter()->sortBy('display_name')->values())
                        @php($scopeLabel = $scopeClasses->count() > 1 ? $scopeClasses->pluck('display_name')->implode(' + ') : ($scopeClasses->first()?->display_name ?? $session->academicClass?->display_name ?? 'Kelas'))
                        <div>
                            <strong>{{ $scopeLabel }} · {{ $session->teachingAssignment?->subject?->subject_name ?? 'Pelajaran' }}</strong>@if ($scopeClasses->count() > 1)<small class="review-joint-label">Kelas gabungan</small>@endif
                            <small>{{ \App\Shared\Platform\Presentation\AcademicBusinessTime::dateTime($session->planned_start_at) }} · Hadir {{ $summary->get('PRESENT', 0) }} · Sakit {{ $summary->get('SICK', 0) }} · Izin {{ $summary->get('IZIN', 0) }} · Tidak hadir {{ $summary->get('ABSENT', 0) }} · Belum diisi {{ $summary->get('PENDING', 0) }}</small>
                        </div>
                        <a href="{{ route('academic.attendance.review', $session) }}">Periksa hasil</a>
                    </div>
                @endforeach
            </div>
            {{ $sessions->links() }}
        @endif
    </section>
    <section class="card">
        <div class="section-heading"><div><p class="eyebrow">Koreksi kehadiran</p><h2>Permintaan menunggu tindakan</h2></div></div>
        @if ($correctionRequests->isEmpty())
            <p class="muted">Tidak ada permintaan koreksi aktif. Koreksi yang dilakukan langsung oleh Waka Akademik sudah diterapkan saat formulir dikirim dan tidak menunggu daftar persetujuan.</p>
        @else
            <div class="session-list">
                @foreach ($correctionRequests as $correction)
                    <div class="session-item">
                        <div>
                            <strong>Koreksi {{ $correction->status === 'APPROVED' ? 'disetujui' : 'menunggu tinjauan' }}</strong>
                            <small>Alasan: {{ $correction->reason }} · Status baru: {{ data_get($correction->requested_changes, 'changes.attendance_status', '—') }}</small>
                        </div>
                        @if ($correction->status === 'PENDING')
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap"><form method="POST" action="{{ route('academic.attendance.corrections.review', $correction) }}">@csrf<input type="hidden" name="approve" value="1"><button class="button" type="submit">Setujui</button></form><form method="POST" action="{{ route('academic.attendance.corrections.review', $correction) }}">@csrf<input type="hidden" name="approve" value="0"><input type="hidden" name="rejection_reason" value="Perlu diperbaiki"><button class="button" type="submit" style="background:#a94b1b">Tolak</button></form></div>
                        @else
                            <form method="POST" action="{{ route('academic.attendance.corrections.apply', $correction) }}">@csrf<button class="button" type="submit">Terapkan</button></form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</main></div>
</body>
</html>
