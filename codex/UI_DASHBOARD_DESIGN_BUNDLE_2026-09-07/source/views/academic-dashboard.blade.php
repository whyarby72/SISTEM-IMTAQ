<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ringkasan Akademik</title>
    <style>@include('admin.partials.styles') main{max-width:80rem}.stat{padding:.9rem;background:#f5faf7;border:1px solid #e0eee5;border-radius:.7rem}.stat strong{display:block;font-size:1.5rem;color:#176b4d;margin:.25rem 0}.academic-nav{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}.academic-nav a{color:#176b4d;text-decoration:none;font-weight:700}.academic-nav form{margin:0}.academic-nav button{border:0;background:transparent;color:#176b4d;font:inherit;font-weight:700;cursor:pointer}</style>
</head>
<body><main>@php($roleLabels = ['SUPER_ADMIN' => 'Super Admin', 'WAKA_AKADEMIK' => 'Waka Akademik', 'WALI_KELAS' => 'Wali Kelas'])<nav class="academic-nav"><div><a href="{{ route('academic.dashboard') }}">Ringkasan Akademik</a>@if ($dashboard['role'] !== 'WALI_KELAS') · <a href="{{ route('academic.attendance.exceptions') }}">Perlu Perhatian Kehadiran</a>@endif</div><div class="muted">{{ $roleLabels[$dashboard['role']] ?? $dashboard['role'] }} <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf<button type="submit">Keluar</button></form></div></nav>
    <section class="card"><p class="eyebrow">Ringkasan akademik</p><h1>Ringkasan Akademik</h1><div class="muted">Peran: {{ $roleLabels[$dashboard['role']] ?? $dashboard['role'] }} · {{ $from->format('d M Y') }}–{{ $to->format('d M Y') }} · Hanya melihat</div><p><a class="back" href="{{ route('academic.dashboard.export', request()->query()) }}">Unduh rekap CSV</a> · <a class="back" href="{{ route('academic.monthly-reports.index') }}">Lihat laporan Juli 2026</a></p></section>
    @forelse ($dashboard['classes'] as $item)
        <section class="card"><h2>@uiLabel($item['class']->display_name)</h2>
            <div class="grid">
                <div class="stat"><span class="muted">Kelengkapan kehadiran</span><strong>{{ $item['attendance']['completeness_rate'] ?? '—' }}%</strong><span class="muted">{{ $item['attendance']['resolved_opportunities'] }}/{{ $item['attendance']['eligible_opportunities'] }} sudah terisi</span></div>
                <div class="stat"><span class="muted">Kehadiran fisik</span><strong>{{ $item['attendance']['physical_presence_rate'] ?? '—' }}%</strong></div>
                <div class="stat"><span class="muted">Penyelesaian sesi</span><strong>{{ $item['sessions']['completion_rate'] ?? '—' }}%</strong><span class="muted">{{ $item['sessions']['completed_sessions'] }}/{{ $item['sessions']['counted_sessions'] }} selesai</span></div>
                <div class="stat"><span class="muted">Sesi tambahan</span><strong>{{ $item['sessions']['extra_sessions'] }}</strong></div>
            </div>
            @if ($semester !== null)
                <h3>Nilai — @uiLabel($semester->display_name)</h3>
                <div class="grid">
                    @foreach ($item['grades'] as $grade)
                        <div class="stat"><span class="muted">@uiLabel($grade['subject']->subject_name)</span><strong>{{ $grade['mean_score'] ?? '—' }}</strong><span class="muted">{{ $grade['locked_count'] }}/{{ $grade['expected_count'] }} terkunci · {{ $grade['is_official'] ? 'Lengkap' : 'Belum lengkap' }}</span></div>
                    @endforeach
                </div>
            @endif
        </section>
    @empty
        <section class="card"><p class="muted">Tidak ada kelas dalam scope Anda.</p></section>
    @endforelse
</main></body></html>
