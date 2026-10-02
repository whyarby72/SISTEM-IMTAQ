@php
    $authUser = auth()->user();
    $resolver = app(\App\Shared\Platform\Authorization\Services\FeatureAccessResolver::class);
    $roleCode = $roleCodeOverride ?? $authUser?->roles?->pluck('code')->first();
    $roleLabels = ['SUPER_ADMIN' => 'Super Admin', 'WAKA_AKADEMIK' => 'Waka Akademik', 'WALI_KELAS' => 'Wali Kelas'];
    $roleLabel = $roleLabels[$roleCode] ?? $authUser?->roles?->pluck('name')->first() ?? 'Pengguna akademik';
    $activeMenu = $activeMenu ?? '';
    $dashboardUrl = route('academic.dashboard', request()->only(['month', 'from', 'to', 'trend_days', 'semester_id']));
    $allowed = static fn (string $code): bool => $authUser && $resolver->allowed($authUser, $code);
    $sidebarPreference = $authUser?->preferences?->firstWhere('preference_key', 'sidebar_compact');
    $sidebarPreferenceValue = $sidebarPreference?->preference_value;
    $sidebarCompact = (bool) (is_array($sidebarPreferenceValue) ? ($sidebarPreferenceValue['value'] ?? false) : false);
@endphp
<style>
    .waka-sidebar.is-compact:hover,.waka-sidebar.is-compact:focus-within{width:4.4rem;padding-left:.55rem;padding-right:.55rem;box-shadow:none}
    .waka-sidebar.is-compact:hover .waka-brand span:not(.waka-mark),.waka-sidebar.is-compact:focus-within .waka-brand span:not(.waka-mark),.waka-sidebar.is-compact:hover .waka-nav a span:not(.nav-icon),.waka-sidebar.is-compact:focus-within .waka-nav a span:not(.nav-icon),.waka-sidebar.is-compact:hover .waka-sidebar-footer,.waka-sidebar.is-compact:focus-within .waka-sidebar-footer{display:none}
    .waka-sidebar.is-compact:hover .waka-nav a,.waka-sidebar.is-compact:focus-within .waka-nav a{justify-content:center}
</style>
<aside class="waka-sidebar{{ $sidebarCompact ? ' is-compact' : '' }}" aria-label="Navigasi akademik">
    <button class="waka-sidebar-toggle" type="button" aria-expanded="false" aria-controls="waka-nav" aria-label="Buka navigasi" title="Buka navigasi">☰</button>
    <a class="waka-brand" href="{{ $dashboardUrl }}" aria-label="IMTAQ — Dashboard akademik" title="IMTAQ — Dashboard akademik"><img class="waka-mark" src="{{ asset('images/logo-imtaq.png') }}" alt="Logo IMTAQ"><span class="waka-brand-text">IMTAQ<small>Sistem Akademik</small></span></a>
    <nav class="waka-nav" id="waka-nav">
        @if ($allowed('academic.dashboard'))<a class="{{ $activeMenu === 'dashboard' ? 'active' : '' }}" href="{{ $dashboardUrl }}" aria-label="Beranda" title="Beranda" @if ($activeMenu === 'dashboard') aria-current="page" @endif><span class="nav-icon" aria-hidden="true">⌂</span><span>Beranda</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.attendance'))<a class="{{ $activeMenu === 'attendance' ? 'active' : '' }}" href="{{ $attendanceUrl ?? route('academic.attendance.exceptions') }}" aria-label="Kontrol Kehadiran" title="Kontrol Kehadiran"><span class="nav-icon" aria-hidden="true">◷</span><span>Kontrol Kehadiran</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.attendance_review'))<a class="{{ $activeMenu === 'attendance-reviews' ? 'active' : '' }}" href="{{ route('academic.attendance.reviews') }}" aria-label="Hasil dan Koreksi" title="Hasil dan Koreksi"><span class="nav-icon" aria-hidden="true">✓</span><span>Hasil &amp; Koreksi</span></a>@endif
        @if ($allowed('academic.dashboard'))<a class="{{ $activeMenu === 'monitoring' ? 'active' : '' }}" href="{{ $dashboardUrl }}#pemantauan" aria-label="Pemantauan Kelas" title="Pemantauan Kelas"><span class="nav-icon" aria-hidden="true">◉</span><span>Pemantauan Kelas</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.students'))<a class="{{ $activeMenu === 'students' ? 'active' : '' }}" href="{{ route('admin.academic.students.index') }}" aria-label="Santri" title="Santri"><span class="nav-icon" aria-hidden="true">♧</span><span>Santri</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.classes'))<a class="{{ $activeMenu === 'classes' ? 'active' : '' }}" href="{{ route('admin.academic.classes.index') }}" aria-label="Kelas" title="Kelas"><span class="nav-icon" aria-hidden="true">▥</span><span>Kelas</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.structure'))<a class="{{ $activeMenu === 'structure' ? 'active' : '' }}" href="{{ route('admin.academic.structure.index') }}" aria-label="Struktur" title="Struktur"><span class="nav-icon" aria-hidden="true">⌘</span><span>Struktur</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.schedules'))<a class="{{ $activeMenu === 'schedules' ? 'active' : '' }}" href="{{ route('admin.academic.schedules.index') }}" aria-label="Jadwal" title="Jadwal"><span class="nav-icon" aria-hidden="true">▦</span><span>Jadwal</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.subjects'))<a class="{{ $activeMenu === 'subjects' ? 'active' : '' }}" href="{{ route('admin.academic.subjects.index') }}" aria-label="Mata Pelajaran" title="Mata Pelajaran"><span class="nav-icon" aria-hidden="true">◇</span><span>Mata Pelajaran</span></a>@endif
        @if ($roleCode !== 'WALI_KELAS' && $allowed('academic.staff'))<a class="{{ $activeMenu === 'staff' ? 'active' : '' }}" href="{{ route('admin.academic.staff.index') }}" aria-label="Guru dan Staf" title="Guru dan Staf"><span class="nav-icon" aria-hidden="true">♙</span><span>Guru/Staf</span></a>@endif
        @if ($roleCode === 'SUPER_ADMIN' && $allowed('platform.user_access'))<a class="{{ $activeMenu === 'users' ? 'active' : '' }}" href="{{ route('admin.system.users.index') }}" aria-label="User dan Akses" title="User dan Akses"><span class="nav-icon" aria-hidden="true">♙</span><span>User &amp; Akses</span></a>@endif
        @if ($roleCode === 'SUPER_ADMIN' && $allowed('platform.system_settings'))<a class="{{ $activeMenu === 'settings' ? 'active' : '' }}" href="{{ route('admin.system.ai-provider.index') }}" aria-label="Pengaturan Sistem" title="Pengaturan Sistem"><span class="nav-icon" aria-hidden="true">⚙</span><span>Pengaturan Sistem</span></a>@endif
        @if ($allowed('academic.reports'))<a class="{{ $activeMenu === 'reports' ? 'active' : '' }}" href="{{ route('academic.monthly-reports.index') }}" aria-label="Laporan Juli 2026" title="Laporan Juli 2026"><span class="nav-icon" aria-hidden="true">▤</span><span>Laporan Juli 2026</span></a>@endif
    </nav>
    <div class="waka-sidebar-footer"><div class="waka-sidebar-account"><span class="waka-sidebar-avatar">{{ strtoupper(substr($roleLabel, 0, 2)) }}</span><strong>{{ $roleLabel }}</strong></div><small>Tahun ajaran aktif<br>{{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</small><form method="POST" action="{{ route('logout') }}">@csrf<button class="waka-sidebar-logout" type="submit">Keluar</button></form></div>
</aside>
<script>
document.addEventListener('click',function(event){const sidebar=event.target.closest('.waka-sidebar');if(window.matchMedia('(max-width: 680px)').matches){if(!sidebar){document.querySelectorAll('.waka-sidebar').forEach(function(item){item.classList.remove('is-expanded');const toggle=item.querySelector('.waka-sidebar-toggle');if(toggle){toggle.setAttribute('aria-expanded','false');toggle.setAttribute('aria-label','Buka navigasi');toggle.setAttribute('title','Buka navigasi')}});return}const toggle=event.target.closest('.waka-sidebar-toggle');if(toggle){const expanded=sidebar.classList.toggle('is-expanded');toggle.setAttribute('aria-expanded',expanded?'true':'false');toggle.setAttribute('aria-label',expanded?'Tutup navigasi':'Buka navigasi');toggle.setAttribute('title',expanded?'Tutup navigasi':'Buka navigasi');return}document.querySelectorAll('.waka-sidebar').forEach(function(item){item.classList.toggle('is-expanded',item===sidebar)})}});
</script>
