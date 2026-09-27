<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detail Kehadiran {{ $class->display_name }}</title>
    <style>
        @include('admin.partials.styles')
        .hero-card { padding: 1.6rem; }
        .hero-card .muted { max-width: 42rem; margin-bottom: 0; }
        .summary { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.85rem; }
        .summary-item { padding:1rem; border:1px solid #d6e8dc; border-radius:.8rem; background:linear-gradient(145deg,#f7fcf8,#edf7f0); }
        .summary-item span { display:block; color:#61776d; font-size:.82rem; font-weight:700; }
        .summary-item strong { display:block; margin-top:.3rem; color:#176b4d; font-size:1.55rem; }
        .detail-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
        .detail-heading h2 { margin:0; font-size:1.3rem; }
        .detail-heading p { margin:.3rem 0 0; }
        .detail-table { min-width:58rem; }
        .detail-table th, .detail-table td { padding:.9rem .7rem; }
        .detail-table th { background:#f4faf6; border-bottom:1px solid #cfe3d6; }
        .detail-table tbody tr:nth-child(even) { background:#fbfefc; }
        .detail-table tbody tr:hover { background:#eef8f1; }
        .name-arab { direction:rtl; text-align:right; font-size:1.1rem; white-space:nowrap; }
        .rate { color:#176b4d; font-weight:800; white-space:nowrap; }
        .student-cards { display:none; }
        .student-card { border:1px solid #d6e8dc; border-radius:.85rem; padding:1rem; background:#fbfefc; }
        .student-card + .student-card { margin-top:.75rem; }
        .student-card-header { display:flex; justify-content:space-between; gap:.75rem; align-items:flex-start; padding-bottom:.75rem; border-bottom:1px solid #e1eee5; }
        .student-card-header strong { display:block; }
        .student-card-header .name-arab { font-size:1rem; }
        .student-card-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.7rem 1rem; padding-top:.85rem; }
        .student-card-grid span { display:block; color:#61776d; font-size:.78rem; font-weight:700; }
        .student-card-grid strong { display:block; margin-top:.2rem; font-size:1.05rem; }
        @media(max-width:860px) { .summary { grid-template-columns:repeat(3,minmax(0,1fr)); } }
        @media(max-width:640px) {
            .hero-card { padding:1.2rem; }
            .summary { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; }
            .summary-item { padding:.8rem; }
            .summary-item strong { font-size:1.35rem; }
            .detail-heading { display:block; }
            .detail-heading p { margin-top:.45rem; }
            .desktop-detail { display:none; }
            .student-cards { display:block; }
        }
    </style>
</head>
<body>
<main>
    <header class="topbar">
        <a class="brand" href="{{ route($role === 'WALI_KELAS' ? 'academic.monthly-reports.index' : 'admin.academic.monthly-reports.index') }}">SISTEM IMTAQ <small>Detail Laporan Bulanan</small></a>
    </header>

    <section class="card hero-card">
        <p class="eyebrow">Laporan per kelas · Juli 2026</p>
        <div class="actions"><div><h1>@uiLabel($class->display_name)</h1><p class="muted">Rincian kehadiran setiap santri berdasarkan rekap Juli 2026.</p></div><a class="button" href="{{ route($role === 'WALI_KELAS' ? 'academic.monthly-reports.index' : 'admin.academic.monthly-reports.index') }}">Kembali</a></div>
    </section>

    <section class="card">
        <div class="summary">
            <div class="summary-item"><span>Jumlah santri</span><strong>{{ $summary->roster }}</strong></div>
            <div class="summary-item"><span>Hadir</span><strong>{{ $summary->present }}</strong></div>
            <div class="summary-item"><span>Izin</span><strong>{{ $summary->permission }}</strong></div>
            <div class="summary-item"><span>Sakit</span><strong>{{ $summary->sick }}</strong></div>
            <div class="summary-item"><span>Absen</span><strong>{{ $summary->absent }}</strong></div>
        </div>
    </section>

    <section class="card">
        @if (! $detailAvailable)
            <p class="notice">Rincian per santri belum tersedia di lingkungan ini. Rekap kelas tetap dapat dilihat.</p>
        @elseif ($students->isEmpty())
            <p class="notice">Rincian per santri belum tersedia untuk kelas ini.</p>
        @else
            <div class="detail-heading"><div><h2>Rincian kehadiran santri</h2><p class="muted">Rekap historis Juli 2026 · hanya baca</p></div><span class="status">{{ $students->count() }} santri</span></div>
            <div class="table-wrap desktop-detail">
                <table class="detail-table"><thead><tr><th>No</th><th>Santri</th><th>Nama Arab</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Absen</th><th>Keaktifan</th></tr></thead><tbody>
                @foreach ($students as $index => $student)
                    <tr><td>{{ $index + 1 }}</td><td><strong>{{ $student->name_indonesia ?? '—' }}</strong><br><small class="muted">{{ $student->source_record_id }}</small></td><td class="name-arab">{{ $student->name_arabic ?? '—' }}</td><td>{{ $student->present }}</td><td>{{ $student->permission }}</td><td>{{ $student->sick }}</td><td>{{ $student->absent }}</td><td class="rate">{{ number_format((float) $student->attendance_rate * 100, 2, ',', '.') }}%</td></tr>
                @endforeach
                </tbody></table>
            </div>
            <div class="student-cards">
                @foreach ($students as $index => $student)
                    <article class="student-card"><div class="student-card-header"><div><small class="muted">{{ $index + 1 }} · {{ $student->source_record_id }}</small><strong>{{ $student->name_indonesia ?? '—' }}</strong></div><div class="name-arab">{{ $student->name_arabic ?? '—' }}</div></div><div class="student-card-grid"><div><span>Hadir</span><strong>{{ $student->present }}</strong></div><div><span>Izin</span><strong>{{ $student->permission }}</strong></div><div><span>Sakit</span><strong>{{ $student->sick }}</strong></div><div><span>Absen</span><strong>{{ $student->absent }}</strong></div><div><span>Keaktifan</span><strong class="rate">{{ number_format((float) $student->attendance_rate * 100, 2, ',', '.') }}%</strong></div></div></article>
                @endforeach
            </div>
        @endif
    </section>
</main>
</body>
</html>
