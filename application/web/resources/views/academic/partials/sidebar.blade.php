@php
    $roleCode = $roleCodeOverride ?? auth()->user()?->roles?->pluck('code')->first();
    $roleLabels = ['SUPER_ADMIN' => 'Super Admin', 'WAKA_AKADEMIK' => 'Waka Akademik', 'WALI_KELAS' => 'Wali Kelas'];
    $roleLabel = $roleLabels[$roleCode] ?? auth()->user()?->roles?->pluck('name')->first() ?? 'Pengguna akademik';
    $activeMenu = $activeMenu ?? '';
    $dashboardUrl = route('academic.dashboard', request()->only(['month', 'from', 'to', 'trend_days', 'semester_id']));
@endphp
<style>
    .waka-sidebar-toggle { display: none; }
    @media (max-width: 680px) {
        .waka-sidebar-toggle { display: grid; place-items: center; width: 2.35rem; height: 2.35rem; margin: 0 auto .75rem; border: 1px solid rgba(255,255,255,.3); border-radius: .6rem; background: rgba(255,255,255,.08); color: #fff; font: inherit; font-size: 1.05rem; cursor: pointer; }
        .waka-sidebar.is-expanded .waka-sidebar-toggle { margin-left: .75rem; margin-right: 0; }
        .waka-sidebar-toggle:hover, .waka-sidebar-toggle:focus-visible { background: #f4fff8; color: #07553f; }
    }
</style>
<aside class="waka-sidebar" aria-label="Navigasi akademik">
    <button class="waka-sidebar-toggle" type="button" aria-expanded="false" aria-controls="waka-nav" aria-label="Buka navigasi" title="Buka navigasi">☰</button>
    <a class="waka-brand" href="{{ $dashboardUrl }}" aria-label="IMTAQ — Dashboard akademik" title="IMTAQ — Dashboard akademik"><img class="waka-mark" src="{{ asset('images/logo-imtaq.png') }}" alt="Logo IMTAQ"><span class="waka-brand-text">IMTAQ<small>Sistem Akademik</small></span></a>
    <nav class="waka-nav" id="waka-nav">
        <a class="{{ $activeMenu === 'dashboard' ? 'active' : '' }}" href="{{ $dashboardUrl }}" aria-label="Beranda" title="Beranda" @if ($activeMenu === 'dashboard') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">⌂</span><span>Beranda</span></a>
        @if ($roleCode !== 'WALI_KELAS')<a class="{{ $activeMenu === 'attendance' ? 'active' : '' }}" href="{{ $attendanceUrl ?? route('academic.attendance.exceptions') }}" aria-label="Kontrol Kehadiran" title="Kontrol Kehadiran" @if ($activeMenu === 'attendance') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">◷</span><span>Kontrol Kehadiran</span></a><a class="{{ $activeMenu === 'attendance-reviews' ? 'active' : '' }}" href="{{ route('academic.attendance.reviews') }}" aria-label="Hasil dan Koreksi" title="Hasil dan Koreksi" @if ($activeMenu === 'attendance-reviews') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">✓</span><span>Hasil &amp; Koreksi</span></a>@endif
        <a class="{{ $activeMenu === 'monitoring' ? 'active' : '' }}" href="{{ $dashboardUrl }}#pemantauan" aria-label="Pemantauan Kelas" title="Pemantauan Kelas" @if ($activeMenu === 'monitoring') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">◉</span><span>Pemantauan Kelas</span></a>
        @if ($roleCode !== 'WALI_KELAS')<a class="{{ $activeMenu === 'students' ? 'active' : '' }}" href="{{ route('admin.academic.students.index') }}" aria-label="Santri" title="Santri" @if ($activeMenu === 'students') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">♧</span><span>Santri</span></a><a class="{{ $activeMenu === 'classes' ? 'active' : '' }}" href="{{ route('admin.academic.classes.index') }}" aria-label="Kelas" title="Kelas" @if ($activeMenu === 'classes') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">▥</span><span>Kelas</span></a><a class="{{ $activeMenu === 'structure' ? 'active' : '' }}" href="{{ route('admin.academic.structure.index') }}" aria-label="Struktur" title="Struktur" @if ($activeMenu === 'structure') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">⌘</span><span>Struktur</span></a><a class="{{ $activeMenu === 'schedules' ? 'active' : '' }}" href="{{ route('admin.academic.schedules.index') }}" aria-label="Jadwal" title="Jadwal" @if ($activeMenu === 'schedules') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">▦</span><span>Jadwal</span></a><a class="{{ $activeMenu === 'subjects' ? 'active' : '' }}" href="{{ route('admin.academic.subjects.index') }}" aria-label="Mata Pelajaran" title="Mata Pelajaran" @if ($activeMenu === 'subjects') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">◇</span><span>Mata Pelajaran</span></a><a class="{{ $activeMenu === 'staff' ? 'active' : '' }}" href="{{ route('admin.academic.staff.index') }}" aria-label="Guru dan Staf" title="Guru dan Staf" @if ($activeMenu === 'staff') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">♙</span><span>Guru/Staf</span></a>@endif
        @if ($roleCode === 'SUPER_ADMIN')<a class="{{ $activeMenu === 'settings' ? 'active' : '' }}" href="{{ route('admin.system.ai-provider.index') }}" aria-label="Pengaturan Sistem" title="Pengaturan Sistem" @if ($activeMenu === 'settings') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">⚙</span><span>Pengaturan Sistem</span></a>@endif
        <a class="{{ $activeMenu === 'reports' ? 'active' : '' }}" href="{{ route('academic.monthly-reports.index') }}" aria-label="Laporan Juli 2026" title="Laporan Juli 2026" @if ($activeMenu === 'reports') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">▤</span><span>Laporan Juli 2026</span></a>
    </nav>
    <div class="waka-sidebar-footer">
        <div class="waka-sidebar-account"><span class="waka-sidebar-avatar">{{ strtoupper(substr($roleLabel, 0, 2)) }}</span><strong>{{ $roleLabel }}</strong></div>
        <small>Tahun ajaran aktif<br>{{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</small>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="waka-sidebar-logout" type="submit">Keluar</button></form>
    </div>
</aside>
<script>
document.addEventListener('click', function (event) {
    const sidebar = event.target.closest('.waka-sidebar');
    if (window.matchMedia('(max-width: 680px)').matches) {
        if (!sidebar) {
            document.querySelectorAll('.waka-sidebar').forEach(function (item) {
                item.classList.remove('is-expanded');
                const toggle = item.querySelector('.waka-sidebar-toggle');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.setAttribute('aria-label', 'Buka navigasi');
                    toggle.setAttribute('title', 'Buka navigasi');
                }
            });
            return;
        }
        const toggle = event.target.closest('.waka-sidebar-toggle');
        if (toggle) {
            const expanded = sidebar.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.setAttribute('aria-label', expanded ? 'Tutup navigasi' : 'Buka navigasi');
            toggle.setAttribute('title', expanded ? 'Tutup navigasi' : 'Buka navigasi');
            return;
        }
        document.querySelectorAll('.waka-sidebar').forEach(function (item) {
            item.classList.toggle('is-expanded', item === sidebar);
        });
    }
});
document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape' || !window.matchMedia('(max-width: 680px)').matches) {
        return;
    }
    document.querySelectorAll('.waka-sidebar.is-expanded').forEach(function (item) {
        item.classList.remove('is-expanded');
        const toggle = item.querySelector('.waka-sidebar-toggle');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Buka navigasi');
            toggle.setAttribute('title', 'Buka navigasi');
            toggle.focus();
        }
    });
});
</script>
@if (request()->is('admin/academic/structure*'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[name="effective_from"], input[name="effective_until"]').forEach(function (input) {
        var value = input.value;
        input.type = 'text';
        input.placeholder = 'dd/mm/yyyy';
        input.inputMode = 'numeric';
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            var parts = value.split('-');
            input.value = parts[2] + '/' + parts[1] + '/' + parts[0];
        }
    });
});
</script>
@endif
