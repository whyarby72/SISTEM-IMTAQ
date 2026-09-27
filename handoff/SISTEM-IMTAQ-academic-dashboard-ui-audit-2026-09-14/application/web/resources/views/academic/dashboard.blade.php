<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ringkasan Akademik</title>
    <style>
        @include('admin.partials.styles')
        :root{--waka-green:#086b4f;--waka-deep:#07553f;--waka-soft:#e8f6ef;--waka-blue:#e9f2ff;--waka-amber:#fff3df;--waka-purple:#f0edff;--waka-ink:#12372d;--waka-muted:#6a8278}
        body{padding:0;background:#f4f8f6;color:var(--waka-ink)}
        .waka-shell{display:grid;grid-template-columns:4.4rem minmax(0,1fr);min-height:100vh}
        .waka-sidebar{display:flex;flex-direction:column;background:linear-gradient(180deg,var(--waka-deep),#063d31);color:#ecfff5;padding:1.35rem .55rem;position:sticky;top:0;height:100vh;width:4.4rem;z-index:10;transition:width .2s ease,padding .2s ease;overflow:hidden}
        .waka-sidebar:hover,.waka-sidebar:focus-within{width:14rem;padding-left:1rem;padding-right:1rem;box-shadow:10px 0 24px rgba(6,61,49,.16)}
        .waka-brand{display:flex;gap:.65rem;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-weight:800;font-size:1.15rem;letter-spacing:-.03em;padding:.25rem .45rem 1.8rem;white-space:nowrap}
        .waka-brand small{display:block;color:#bde4d0;font-size:.65rem;letter-spacing:.08em;text-transform:uppercase;margin-top:.15rem}
        .waka-mark{width:2.75rem;height:2.75rem;border-radius:.35rem;object-fit:contain;background:#fff;display:block;flex:none}
        .waka-nav{display:grid;gap:.35rem}.waka-nav a{display:flex;align-items:center;gap:.65rem;color:#d1eee0;text-decoration:none;padding:.7rem .75rem;border-radius:.65rem;font-size:.86rem}.waka-nav a:hover,.waka-nav a.active{background:#f4fff8;color:var(--waka-deep);font-weight:800}.waka-nav .nav-icon{width:1.35rem;text-align:center;font-size:1.2rem;line-height:1.1}
        .waka-brand-text,.waka-nav a span:not(.nav-icon),.waka-sidebar-footer{display:none}.waka-sidebar:hover .waka-brand-text,.waka-sidebar:focus-within .waka-brand-text,.waka-sidebar:hover .waka-nav a span:not(.nav-icon),.waka-sidebar:focus-within .waka-nav a span:not(.nav-icon),.waka-sidebar:hover .waka-sidebar-footer,.waka-sidebar:focus-within .waka-sidebar-footer{display:block}.waka-sidebar:hover .waka-nav a,.waka-sidebar:focus-within .waka-nav a,.waka-sidebar:hover .waka-brand,.waka-sidebar:focus-within .waka-brand{justify-content:flex-start;padding-left:.75rem}
        .waka-sidebar-footer{margin-top:auto;border-top:1px solid rgba(255,255,255,.18);padding-top:1rem;color:#bde4d0;font-size:.75rem;line-height:1.45;white-space:nowrap}.waka-sidebar-account{display:flex;align-items:center;gap:.55rem;margin-bottom:.55rem}.waka-sidebar-account strong{display:block;color:#fff;font-size:.88rem}.waka-sidebar-avatar{width:1.85rem;height:1.85rem;border-radius:50%;display:grid;place-items:center;background:#ccebdc;color:var(--waka-green);font-size:.68rem;font-weight:900;flex:none}.waka-sidebar-footer small{display:block}.waka-sidebar-logout{width:100%;margin-top:.8rem;border:1px solid rgba(255,255,255,.3);border-radius:.5rem;background:rgba(255,255,255,.08);color:#fff;padding:.45rem .6rem;font:inherit;font-weight:800;text-align:left;cursor:pointer}.waka-sidebar-logout:hover,.waka-sidebar-logout:focus-visible{background:#f4fff8;color:var(--waka-deep)}
        .waka-content{min-width:0;padding:1.45rem 1.65rem 2.25rem}.waka-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.55rem}.waka-kicker{margin:0;color:var(--waka-green);font-size:.7rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.waka-title{font-size:clamp(1.55rem,2.8vw,2.2rem);margin:.15rem 0 .2rem;letter-spacing:-.05em}.waka-subtitle{color:var(--waka-muted);margin:0;font-size:.92rem}.waka-user{display:flex;align-items:center;gap:.6rem;color:var(--waka-muted);font-size:.82rem;white-space:nowrap}.waka-avatar{width:2.35rem;height:2.35rem;border-radius:50%;display:grid;place-items:center;background:#ccebdc;color:var(--waka-green);font-weight:900}.waka-logout{border:0;background:transparent;color:var(--waka-green);font:inherit;font-weight:800;cursor:pointer;margin-left:.3rem}
        .waka-filter{display:grid;grid-template-columns:minmax(15rem,1.35fr) repeat(2,minmax(10rem,1fr)) auto;align-items:start;gap:.9rem;background:#fff;border:1px solid #dceae2;border-radius:1rem;padding:1rem 1.1rem;margin-bottom:1.45rem;box-shadow:0 8px 24px rgba(16,72,51,.05)}.waka-filter label{display:flex;flex-direction:column;gap:.35rem;margin:0;color:var(--waka-muted);font-size:.74rem;font-weight:800}.waka-filter input,.waka-filter select{width:100%;min-width:0;margin-top:0;padding:.48rem .6rem}.waka-filter-help{display:block;max-width:14rem;color:var(--waka-muted);font-size:.68rem;font-weight:500;line-height:1.35}.waka-filter button{align-self:start;display:inline-flex;align-items:center;justify-content:center;min-width:8.2rem;height:2.2rem;margin-top:1.25rem;padding:0 .8rem;border-radius:.68rem;box-shadow:0 5px 12px rgba(8,107,79,.16);transition:transform .15s ease,box-shadow .15s ease}.waka-filter button:hover{transform:translateY(-1px);box-shadow:0 8px 16px rgba(8,107,79,.22)}.waka-filter button:active{transform:translateY(0)}.waka-filter button:focus-visible{outline:3px solid #b9e7cf;outline-offset:2px}.waka-filter-note{grid-column:1/-1;margin:0;color:var(--waka-muted);font-size:.75rem;justify-self:end}
        .waka-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.85rem;margin-bottom:1rem}.waka-kpi{display:flex;flex-direction:column;min-height:8.6rem;border:1px solid #dceae2;border-radius:1rem;padding:1rem;background:#fff;box-shadow:0 8px 24px rgba(16,72,51,.05);min-width:0}.waka-kpi.blue{background:var(--waka-blue);border-color:#d6e6fb}.waka-kpi.amber{background:var(--waka-amber);border-color:#f6e0bd}.waka-kpi.purple{background:var(--waka-purple);border-color:#e0d9ff}.waka-kpi.neutral{background:#f5f8f6;border-color:#dfe7e2}.waka-kpi-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem}.waka-kpi-label{color:var(--waka-muted);font-size:.77rem;font-weight:800}.waka-kpi-icon{width:1.85rem;height:1.85rem;border-radius:.55rem;display:grid;place-items:center;background:rgba(255,255,255,.62);color:var(--waka-green);font-weight:900}.waka-kpi.neutral .waka-kpi-icon{color:var(--waka-muted)}.waka-kpi-value{display:block;font-size:1.8rem;line-height:1.1;margin:.45rem 0 .25rem;color:var(--waka-green);letter-spacing:-.05em}.waka-kpi.neutral .waka-kpi-value,.waka-status-value.neutral{color:var(--waka-muted)}.waka-kpi-meta{margin-top:auto;color:var(--waka-muted);font-size:.75rem}
        .waka-main-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(17rem,.34fr);gap:1.2rem;align-items:start}.waka-card{background:#fff;border:1px solid #dceae2;border-radius:1rem;padding:1.1rem;margin-bottom:1.2rem;box-shadow:0 8px 24px rgba(16,72,51,.05)}.waka-card-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1rem}.waka-card-heading h2{margin:0;font-size:1.08rem;letter-spacing:-.03em}.waka-card-heading p{margin:.3rem 0 0;color:var(--waka-muted);font-size:.78rem}.waka-link{color:var(--waka-green);font-weight:800;text-decoration:none;font-size:.78rem;white-space:nowrap}.waka-link.neutral{color:var(--waka-muted);background:#eef3f0;border:1px solid #dce7e1;border-radius:999px;padding:.32rem .55rem}.waka-status-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.waka-status{border:1px solid #e0eee7;border-radius:.8rem;padding:1rem;background:#f8fcfa}.waka-status-title{font-weight:900}.waka-status-value{font-size:1.45rem;font-weight:900;color:var(--waka-green);margin:.3rem 0;overflow-wrap:anywhere}.waka-status-note{display:block;color:var(--waka-muted);font-size:.75rem;line-height:1.45;overflow-wrap:anywhere}.waka-unavailable{border:1px dashed #cbd9d1;background:#f7faf8;color:var(--waka-muted);border-radius:.75rem;padding:1.1rem;font-size:.82rem}.waka-unavailable strong{display:block;color:var(--waka-ink);margin-bottom:.3rem}
        .waka-class-list{display:grid;gap:.65rem}.waka-class-row{border:1px solid #e2eee8;border-radius:.75rem;padding:.75rem;transition:border-color .15s ease,background .15s ease,box-shadow .15s ease}.waka-class-row:hover{border-color:#b9dfc8;background:#fbfefc;box-shadow:0 4px 12px rgba(16,72,51,.05)}.waka-class-row-top{display:flex;justify-content:space-between;gap:.75rem;align-items:center}.waka-class-name{font-weight:900}.waka-class-meta{color:var(--waka-muted);font-size:.76rem;text-align:right}.waka-progress{height:.45rem;background:#e6f0eb;border-radius:99px;overflow:hidden;margin-top:.55rem}.waka-progress span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#27a875,var(--waka-green));width:var(--progress,0%)}.waka-session-list{display:grid;gap:.6rem}.waka-session-day{color:var(--waka-green);font-size:.72rem;font-weight:900;letter-spacing:.04em;margin:.35rem .15rem 0;text-transform:uppercase}.waka-session-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid #dceae2;border-radius:.75rem;padding:.75rem;text-decoration:none;color:var(--waka-ink);background:#f8fcfa}.waka-session-row:hover{border-color:#9bcbb2;background:#effaf3}.waka-session-row strong,.waka-session-row small{display:block}.waka-session-row small{color:var(--waka-muted);font-size:.75rem;margin-top:.2rem}.waka-session-meta{display:flex;justify-content:flex-end;margin-top:.35rem}.waka-session-status{display:inline-flex;border-radius:999px;padding:.18rem .45rem;background:#e5f4eb;color:var(--waka-green);font-size:.68rem;font-weight:800}.waka-session-action{color:var(--waka-green);font-size:.78rem;font-weight:900;white-space:nowrap}
        .waka-trend{display:grid;gap:.65rem}.waka-trend-row{display:grid;grid-template-columns:5rem minmax(0,1fr) minmax(12rem,1.4fr);align-items:center;gap:.7rem}.waka-trend-date,.waka-trend-meta{color:var(--waka-muted);font-size:.73rem}.waka-trend-meta{text-align:right;line-height:1.35}.waka-trend-track{height:.7rem;background:#e6f0eb;border-radius:99px;overflow:hidden}.waka-trend-track span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#27a875,var(--waka-green));width:var(--progress,0%)}
        .waka-trend-options{display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;margin:-.45rem 0 1rem;color:var(--waka-muted);font-size:.73rem}.waka-trend-options .waka-link{padding:.25rem .45rem;border-radius:999px}.waka-trend-options .waka-link.active{background:var(--waka-soft)}
        .waka-session-filters{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin:-.35rem 0 1rem;color:var(--waka-muted);font-size:.73rem}.waka-session-filters .waka-link{padding:.28rem .55rem;border-radius:999px;border:1px solid #dceae2}.waka-session-filters .waka-link.active{background:var(--waka-soft);border-color:#b9dfc8}
        .waka-rail-list{display:grid;gap:.6rem}.waka-rail-item{display:flex;gap:.65rem;align-items:flex-start;border:1px solid #e2eee8;border-radius:.75rem;padding:.75rem}.waka-rail-item .waka-rail-icon{width:1.8rem;height:1.8rem;border-radius:.55rem;background:var(--waka-soft);color:var(--waka-green);display:grid;place-items:center;text-align:center;line-height:1;font-size:1rem;font-weight:900;flex:none}.waka-rail-item .waka-rail-icon.warning{background:#fff0dc;color:#c26b15}.waka-rail-item .waka-rail-icon.success{background:#e2f6e9;color:#198457}.waka-rail-item strong{display:block;font-size:.82rem}.waka-rail-item span{display:block;color:var(--waka-muted);font-size:.73rem;margin-top:.15rem}.waka-rail-item .waka-rail-icon{margin-top:0;color:var(--waka-green)}.waka-actions{display:grid;gap:.55rem}.waka-actions a{display:block;text-align:center}.waka-footnote{color:var(--waka-muted);font-size:.72rem;margin:0}
        @media(max-width:1000px){.waka-sidebar{padding:.9rem .55rem}.waka-sidebar:hover,.waka-sidebar:focus-within{padding-left:.7rem;padding-right:.7rem}.waka-brand{padding-bottom:1.4rem}.waka-nav a{font-size:.8rem}.waka-main-grid{grid-template-columns:1fr}.waka-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.waka-rail .waka-card{margin-bottom:0}}
        @media(max-width:680px){.waka-shell{display:block}.waka-sidebar{position:fixed;left:0;top:0;width:4.4rem;height:100vh;padding:1rem .55rem;overflow:hidden}.waka-sidebar.is-expanded{width:12rem;padding-left:1rem;padding-right:1rem;box-shadow:10px 0 24px rgba(6,61,49,.16)}.waka-brand{padding:.2rem .45rem 1.4rem}.waka-sidebar .waka-brand-text,.waka-sidebar .waka-nav a span:not(.nav-icon),.waka-sidebar .waka-sidebar-footer{display:none}.waka-sidebar.is-expanded .waka-brand-text,.waka-sidebar.is-expanded .waka-nav a span:not(.nav-icon),.waka-sidebar.is-expanded .waka-sidebar-footer{display:block}.waka-sidebar.is-expanded .waka-nav a{justify-content:flex-start}.waka-sidebar.is-expanded .waka-brand{justify-content:flex-start;padding-left:1.75rem}.waka-nav a{justify-content:center;padding:.7rem .55rem}.waka-content{padding:.9rem;margin-left:4.4rem}}
        @media(max-width:900px){.waka-filter{grid-template-columns:minmax(0,1fr) minmax(0,1fr) auto}.waka-filter label:first-child{grid-column:1/-1}.waka-filter button{justify-self:start}}
        @media(max-width:680px){.waka-content{padding:.9rem}.waka-topbar{align-items:flex-start}.waka-user{font-size:0}.waka-user .waka-logout{font-size:.78rem}.waka-grid{grid-template-columns:1fr}.waka-filter{grid-template-columns:1fr;align-items:stretch;gap:.7rem}.waka-filter label:first-child{grid-column:auto}.waka-filter label,.waka-filter input,.waka-filter select,.waka-filter button{width:100%}.waka-filter button{align-self:auto;justify-self:stretch;margin-top:0}.waka-filter-note{grid-column:auto;justify-self:start;padding-top:.15rem}.waka-status-grid,.waka-rail{grid-template-columns:1fr}.waka-card-heading{flex-direction:column;gap:.45rem}.waka-card-heading .waka-link{white-space:normal}.waka-status-value{font-size:1.2rem}.waka-class-row-top{align-items:flex-start;flex-direction:column;gap:.2rem}.waka-class-meta{text-align:left}.waka-trend-row{grid-template-columns:1fr;gap:.35rem}.waka-trend-meta{text-align:left}}
        @media(max-width:680px){.waka-sidebar.is-expanded .waka-brand{justify-content:flex-start;padding-left:.75rem}.waka-sidebar.is-expanded .waka-nav a{padding-left:.75rem;padding-right:.75rem}}
        @media(max-width:680px){.waka-sidebar.is-expanded{width:14rem}}
    </style>
</head>
<body>
@php
    $roleLabels = ['SUPER_ADMIN' => 'Super Admin', 'WAKA_AKADEMIK' => 'Waka Akademik', 'WALI_KELAS' => 'Wali Kelas'];
    $roleLabel = $roleLabels[$dashboard['role']] ?? $dashboard['role'];
    $classes = $dashboard['classes'];
    $classCount = $classes->count();
    $completedSessions = $classes->sum(fn ($item) => (int) ($item['sessions']['completed_sessions'] ?? 0));
    $countedSessions = $classes->sum(fn ($item) => (int) ($item['sessions']['counted_sessions'] ?? 0));
    $extraSessions = $classes->sum(fn ($item) => (int) ($item['sessions']['extra_sessions'] ?? 0));
    $overview = $dashboard['overview'] ?? [];
    $overviewAttendance = $overview['attendance'] ?? [];
    $physicalPresenceRate = $overviewAttendance['physical_presence_rate'] ?? null;
    $completenessRate = $overviewAttendance['completeness_rate'] ?? null;
    $attendanceEligible = (int) ($overviewAttendance['eligible_opportunities'] ?? 0);
    $attendanceResolved = (int) ($overviewAttendance['resolved_opportunities'] ?? 0);
    $attendanceMissing = max(0, $attendanceEligible - $attendanceResolved);
    $todayAttendance = $dashboard['today_attendance'] ?? [];
    $todayCompletionRate = $todayAttendance['completion_rate'] ?? null;
    $hasAttendanceData = $attendanceEligible > 0;
    $hasDueSessions = (int) ($todayAttendance['due_sessions'] ?? 0) > 0;
    $physicalUnavailableLabel = $hasAttendanceData ? 'Belum ada data kehadiran tervalidasi' : 'Belum ada data wajib yang dapat dihitung';
    $completenessUnavailableLabel = 'Belum ada data wajib yang dapat dihitung';
    $physicalPresenceMeta = $attendanceResolved > 0
        ? 'Hadir + terlambat dari '.$attendanceResolved.' data kehadiran tervalidasi'
        : ($hasAttendanceData ? $attendanceEligible.' data wajib · belum ada yang tervalidasi' : 'Belum ada data wajib yang dapat dihitung');
    $statusUnavailableLabel = $hasDueSessions ? 'Belum tersedia' : 'Belum ada sesi jatuh tempo';
    $teacherAttendance = $dashboard['teacher_attendance'] ?? [];
    $teacherPresenceRate = $teacherAttendance['presence_rate'] ?? null;
    $teacherHasSessions = (int) ($teacherAttendance['eligible_participations'] ?? 0) > 0;
    $teacherLabel = $teacherPresenceRate !== null ? $teacherPresenceRate.'% hadir' : ($teacherHasSessions ? 'Belum diisi' : 'Belum ada data kehadiran guru');
    $weekdayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $monthShortNames = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
    $monthLongNames = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
    $formatDashboardDate = static fn ($date, array $months): string => $date->format('d').' '.($months[$date->format('m')] ?? $date->format('M')).' '.$date->format('Y');
    $filterModeLabel = $month !== null ? 'Mode bulan penuh' : 'Mode rentang manual';
@endphp
<div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'dashboard', 'roleCodeOverride' => $dashboard['role']])
    <main class="waka-content">
        <header class="waka-topbar">
            <div><p class="waka-kicker">Ringkasan akademik</p><h1 class="waka-title">Dashboard {{ $roleLabel }}</h1></div>
        </header>
        <form class="waka-filter" method="GET" action="{{ route('academic.dashboard') }}">
            <label>Bulan tahun ajaran<select name="month" title="Jika dipilih, periode bulan mengabaikan tanggal manual" aria-describedby="month-filter-help"><option value="">Pilih bulan</option>@foreach ($months as $availableMonth)<option value="{{ $availableMonth->format('Y-m') }}" @selected(($month ?? null) === $availableMonth->format('Y-m'))>{{ $monthLongNames[$availableMonth->format('m')] ?? $availableMonth->format('F') }} {{ $availableMonth->format('Y') }}</option>@endforeach</select><small id="month-filter-help" class="waka-filter-help">Pilih bulan untuk memakai satu bulan penuh; kosongkan untuk rentang tanggal manual.</small></label>
            <label>Mulai<input lang="id" type="date" name="from" value="{{ $from->format('Y-m-d') }}"></label>
            <label>Sampai<input lang="id" type="date" name="to" value="{{ $to->format('Y-m-d') }}"></label>
            <button class="button" type="submit">Terapkan</button>
            <span class="waka-filter-note">Periode: {{ $formatDashboardDate($from, $monthShortNames) }}–{{ $formatDashboardDate($to, $monthShortNames) }} · {{ $filterModeLabel }}</span>
        </form>
        <section class="waka-grid" aria-label="Ringkasan angka">
            <div class="waka-kpi"><div class="waka-kpi-head"><span class="waka-kpi-label">Santri aktif</span><span class="waka-kpi-icon" aria-hidden="true">◉</span></div><strong class="waka-kpi-value">{{ $overview['active_student_count'] ?? 0 }}</strong><span class="waka-kpi-meta">Dalam kelas yang terpantau</span></div>
            <div class="waka-kpi blue"><div class="waka-kpi-head"><span class="waka-kpi-label">Guru aktif</span><span class="waka-kpi-icon" aria-hidden="true">◎</span></div><strong class="waka-kpi-value">{{ $overview['active_teacher_count'] ?? 0 }}</strong><span class="waka-kpi-meta">Memiliki tugas mengajar aktif</span></div>
            <div class="waka-kpi amber"><div class="waka-kpi-head"><span class="waka-kpi-label">Kehadiran fisik</span><span class="waka-kpi-icon" aria-hidden="true">✓</span></div><strong class="waka-kpi-value">{{ $physicalPresenceRate !== null ? $physicalPresenceRate.'%' : $physicalUnavailableLabel }}</strong><span class="waka-kpi-meta">{{ $physicalPresenceMeta }}</span></div>
            <div class="waka-kpi neutral"><div class="waka-kpi-head"><span class="waka-kpi-label">Kelengkapan data</span><span class="waka-kpi-icon" aria-hidden="true">▦</span></div><strong class="waka-kpi-value">{{ $completenessRate !== null ? $completenessRate.'%' : $completenessUnavailableLabel }}</strong><span class="waka-kpi-meta">{{ $attendanceEligible > 0 ? $attendanceResolved.' dari '.$attendanceEligible.' data wajib sudah tervalidasi · '.$attendanceMissing.' belum tervalidasi' : 'Belum ada data wajib yang dapat dihitung' }}</span></div>
        </section>
        <div class="waka-main-grid">
            <div>
                <section class="waka-card"><div class="waka-card-heading"><div><h2>Status Sesi Periode Terpilih</h2><p>Ringkasan sesi santri berdasarkan rentang waktu dan pengesahan.</p></div><span class="waka-link {{ $todayCompletionRate === null ? 'neutral' : '' }}">{{ $todayCompletionRate !== null ? $todayCompletionRate.'%' : $statusUnavailableLabel }}</span></div><div class="waka-status-grid"><div class="waka-status"><span class="waka-status-title">Pengesahan sesi</span><div class="waka-status-value {{ $todayCompletionRate === null ? 'neutral' : '' }}">{{ $todayCompletionRate !== null ? $todayCompletionRate.'% sesi disahkan' : $statusUnavailableLabel }}</div><span class="waka-status-note">{{ ($todayAttendance['finalized_sessions'] ?? 0).' disahkan · '.($todayAttendance['due_not_finalized_sessions'] ?? 0).' belum disahkan · '.($todayAttendance['in_progress_sessions'] ?? 0).' berlangsung · '.($todayAttendance['upcoming_sessions'] ?? 0).' akan datang' }}</span></div><div class="waka-status"><span class="waka-status-title">Kehadiran guru</span><div class="waka-status-value {{ $teacherPresenceRate === null ? 'neutral' : '' }}">{{ $teacherLabel }}</div><span class="waka-status-note">{{ ($teacherAttendance['resolved_participations'] ?? 0).' dari '.($teacherAttendance['eligible_participations'] ?? 0).' penugasan tercatat · Hadir '.($teacherAttendance['present'] ?? 0).' · Tidak hadir '.($teacherAttendance['absent'] ?? 0).' · Sakit '.($teacherAttendance['sick'] ?? 0).' · Izin '.($teacherAttendance['izin'] ?? 0).' · Lainnya '.($teacherAttendance['other'] ?? 0) }}</span></div></div></section>
                <section class="waka-card" id="pemantauan"><div class="waka-card-heading"><div><h2>Pemantauan Kelas</h2><p>Kelengkapan kehadiran dari data periode terpilih.</p></div><span class="waka-link">{{ $classCount }} kelas</span></div><div class="waka-class-list">
                    @forelse ($classes as $item)
                        @php
                            $rate = is_numeric($item['attendance']['completeness_rate'] ?? null) ? (float) $item['attendance']['completeness_rate'] : null;
                            $eligible = (int) ($item['attendance']['eligible_opportunities'] ?? 0);
                            $resolved = (int) ($item['attendance']['resolved_opportunities'] ?? 0);
                            $missing = max(0, $eligible - $resolved);
                            $className = (string) $item['class']->display_name;
                        @endphp
                        <div class="waka-class-row"><div class="waka-class-row-top"><span class="waka-class-name">@uiLabel($className)</span><span class="waka-class-meta">{{ $eligible > 0 ? $resolved.'/'.$eligible.' data kehadiran tervalidasi · '.$missing.' belum tervalidasi' : 'Belum ada data wajib yang dapat dihitung' }} · {{ $rate !== null ? $rate.'%' : 'Belum tersedia' }}</span></div><div class="waka-progress" role="progressbar" aria-label="Kelengkapan kehadiran {{ $className }}" aria-valuemin="0" aria-valuemax="100" @if ($rate !== null) aria-valuenow="{{ $rate }}" @else aria-valuetext="Belum ada data wajib yang dapat dihitung" @endif><span style="--progress:{{ $rate !== null ? $rate : 0 }}%"></span></div></div>
                    @empty
                        <div class="waka-unavailable"><strong>Tidak ada kelas dalam cakupan</strong>Belum ada data kelas yang dapat ditampilkan.</div>
                    @endforelse
                </div></section>
                @if ($dashboard['role'] === 'WALI_KELAS')
                    @php
                        $attendanceFilter = request('attendance_filter', 'all');
                        $attendanceSessions = $dashboard['attendance_sessions'];
                        $attendanceSessionCounts = [
                            'all' => $attendanceSessions->count(),
                            'empty' => $attendanceSessions->where('attendance_label', 'Belum diisi')->count(),
                            'incomplete' => $attendanceSessions->where('attendance_label', 'Belum lengkap')->count(),
                            'finalized' => $attendanceSessions->where('attendance_label', 'Sudah disahkan')->count(),
                        ];
                        if ($attendanceFilter !== 'all') {
                            $attendanceSessions = $attendanceSessions->filter(fn ($session) => match ($attendanceFilter) {
                                'empty' => $session->attendance_label === 'Belum diisi',
                                'incomplete' => $session->attendance_label === 'Belum lengkap',
                                'finalized' => $session->attendance_label === 'Sudah disahkan',
                                default => true,
                            });
                        }
                        $attendanceFilterLabels = ['all' => 'Semua', 'empty' => 'Belum diisi', 'incomplete' => 'Belum lengkap', 'finalized' => 'Sudah disahkan'];
                    @endphp
                    <section class="waka-card" id="pengisian-kehadiran"><div class="waka-card-heading"><div><h2>Pengisian Kehadiran</h2><p>Pilih sesi untuk mengisi kehadiran santri.</p></div><span class="waka-link">{{ $attendanceSessions->count() }} sesi</span></div>
                        <div class="waka-session-filters" aria-label="Filter status pengisian"><span>Status:</span>@foreach ($attendanceFilterLabels as $filter => $label)<a class="waka-link {{ $attendanceFilter === $filter ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['attendance_filter' => $filter]) }}">{{ $label }} ({{ $attendanceSessionCounts[$filter] }})</a>@endforeach</div>
                        @if ($attendanceSessions->isEmpty())
                            <div class="waka-unavailable"><strong>Belum ada sesi</strong>Belum ada sesi dalam periode yang dipilih.</div>
                        @else
                            <div class="waka-session-list">
                                @php
                                    $previousSessionDate = null;
                                @endphp
                                @foreach ($attendanceSessions as $attendanceSession)
                                    @php
                                        $sessionDate = $attendanceSession->planned_start_at->toDateString();
                                    @endphp
                                    @if ($sessionDate !== $previousSessionDate)
                                        <div class="waka-session-day">{{ $weekdayNames[$attendanceSession->planned_start_at->format('l')] ?? $attendanceSession->planned_start_at->format('l') }}, {{ $attendanceSession->planned_start_at->format('d/m/Y') }}</div>
                                        @php
                                            $previousSessionDate = $sessionDate;
                                        @endphp
                                    @endif
                                    <a class="waka-session-row" href="{{ route('academic.attendance.show', $attendanceSession) }}"><span><strong>@uiLabel($attendanceSession->teachingAssignment?->subject?->subject_name ?? 'Pelajaran')</strong><small>{{ $attendanceSession->planned_start_at->format('d/m/Y, H:i') }} · {{ $attendanceSession->studentParticipants->count() }} santri</small><span class="waka-session-meta"><span class="waka-session-status">{{ $attendanceSession->attendance_label }}</span></span></span><span class="waka-session-action">{{ $attendanceSession->attendance_action }} →</span></a>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
                <section class="waka-card"><div class="waka-card-heading"><div><h2>{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? 'Ringkasan Kehadiran '.($monthLongNames[$from->format('m')] ?? $from->format('F')).' '.$from->format('Y') : 'Tren Kehadiran Santri' }}</h2><p>{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? 'Persentase kehadiran berdasarkan snapshot rekap bulanan.' : 'Persentase hadir + terlambat per hari pada rentang terpilih.' }}</p></div><span class="waka-link">{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? '1 bulan' : count($dashboard['attendance_trend'] ?? []).' hari' }}</span></div>
                    @if (($dashboard['attendance_trend_source'] ?? 'daily_transactions') !== 'monthly_snapshot')<div class="waka-trend-options" aria-label="Rentang grafik"><span>Rentang grafik:</span>@foreach ([7 => '7 hari', 14 => '14 hari', 30 => '30 hari'] as $days => $label)<a class="waka-link {{ (int) request('trend_days', 14) === $days ? 'active' : '' }}" href="{{ route('academic.dashboard', array_merge(request()->except('trend_days'), ['trend_days' => $days])) }}">{{ $label }}</a>@endforeach</div>@endif
                    @if (count($dashboard['attendance_trend'] ?? []))
                        <div class="waka-trend" aria-label="Grafik tren kehadiran santri">
                            @foreach ($dashboard['attendance_trend'] as $trend)
                                @php
                                    $trendDate = \Illuminate\Support\Carbon::parse($trend['date']);
                                @endphp
                                @php
                                    $trendEligible = (int) ($trend['eligible_opportunities'] ?? 0);
                                    $trendResolved = (int) ($trend['resolved_opportunities'] ?? 0);
                                    $trendPhysical = $trend['physical_presence_rate'];
                                    $trendCompleteness = $trend['completeness_rate'];
                                    $trendPhysicalLabel = $trendPhysical !== null ? $trendPhysical.'%' : ($trendEligible > 0 ? 'Belum ada data kehadiran tervalidasi' : 'Belum ada data wajib yang dapat dihitung');
                                @endphp
                                <div class="waka-trend-row"><span class="waka-trend-date">{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? 'Juli 2026' : $trendDate->format('d').' '.($monthShortNames[$trendDate->format('m')] ?? $trendDate->format('M')) }}</span><div class="waka-trend-track" role="progressbar" aria-label="Tren kehadiran santri {{ $trendDate->format('d/m/Y') }}" aria-valuemin="0" aria-valuemax="100" @if ($trendPhysical !== null) aria-valuenow="{{ $trendPhysical }}" @else aria-valuetext="{{ $trendPhysicalLabel }}" @endif><span style="--progress:{{ $trendPhysical ?? 0 }}%" title="{{ $trendPhysicalLabel }}"></span></div><span class="waka-trend-meta">Kehadiran: {{ $trendPhysicalLabel }} · Kelengkapan data: {{ $trendCompleteness !== null ? $trendCompleteness.'%' : 'Belum ada data wajib yang dapat dihitung' }} · {{ $trendResolved }}/{{ $trendEligible }} tervalidasi</span></div>
                            @endforeach
                        </div>
                    @else
                        <div class="waka-unavailable"><strong>Grafik belum tersedia</strong>Belum ada kesempatan kehadiran santri pada periode ini.</div>
                    @endif
                </section>
            </div>
            <aside class="waka-rail">
                <section class="waka-card"><div class="waka-card-heading"><div><h2>Pengingat & Tindak Lanjut</h2><p>Panel disiapkan untuk sumber alert resmi.</p></div></div><div class="waka-rail-list"><div class="waka-rail-item"><span class="waka-rail-icon warning" aria-label="Peringatan">!</span><div><strong>Belum tersedia</strong><span>Belum ada sumber pengingat pada Phase 1.</span></div></div><div class="waka-rail-item"><span class="waka-rail-icon success" aria-label="Aman">✓</span><div><strong>Data tetap aman</strong><span>Tidak ada status yang dibuat-buat.</span></div></div></div></section>
                <section class="waka-card"><div class="waka-card-heading"><div><h2>Agenda Akademik</h2><p>Menunggu sumber kalender akademik.</p></div></div><div class="waka-unavailable"><strong>Belum tersedia</strong>Agenda akan ditampilkan setelah sumber data resmi tersedia.</div></section>
                <section class="waka-card"><div class="waka-card-heading"><div><h2>Unduh Laporan</h2><p>Gunakan laporan yang sudah tersedia.</p></div></div><div class="waka-actions"><a class="button" href="{{ route('academic.dashboard.export', request()->filled('month') ? request()->only(['month', 'semester_id']) : request()->only(['from', 'to', 'semester_id'])) }}">Unduh rekap CSV</a><a class="button" style="background:#e2f4eb;color:var(--waka-green)" href="{{ route('academic.monthly-reports.index') }}">Lihat laporan bulanan</a></div></section>
            </aside>
        </div>
    </main>
</div>
</body>
</html>
