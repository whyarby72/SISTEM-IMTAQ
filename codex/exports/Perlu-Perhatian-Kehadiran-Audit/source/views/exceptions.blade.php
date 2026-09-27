<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perlu Perhatian Kehadiran</title>
    <style>@include('academic.partials.sidebar-styles') .flag{display:inline-block;margin:.15rem .25rem .15rem 0;padding:.25rem .5rem;border-radius:99px;background:#fff1d6;color:#76500b;font-size:.85rem;font-weight:700}.attendance-table{min-width:56rem}.empty{color:#176b45}.attendance-action{display:inline-block;padding:.5rem .75rem;border-radius:.55rem;background:#176b45;color:#fff;text-decoration:none;font-weight:800;white-space:nowrap}.attendance-action:hover,.attendance-action:focus-visible{background:#0d573a}.bulk-cancel{display:grid;gap:.8rem}.bulk-cancel-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr auto;gap:.7rem;align-items:end}.bulk-cancel-submit{display:grid;gap:.8rem;margin-top:.1rem}.bulk-roster-submit{padding:.75rem;background:#f3faf6;border:1px solid #cfe5d8;border-radius:.65rem;margin:0 0 .85rem}.bulk-roster-submit button{background:#176b45!important;justify-self:start!important;width:auto!important}.bulk-cancel label{display:grid;gap:.3rem;color:#345b4e;font-weight:700;font-size:.8rem}.bulk-date-day{color:#176b45;font-size:.9rem;font-weight:800}.bulk-date-picker{position:relative}.bulk-date-picker .bulk-date-display{color:#17352b;background:#fff;padding-right:3rem}.bulk-date-picker .bulk-date-native{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.bulk-date-trigger{position:absolute;right:.6rem;top:50%;transform:translateY(-50%);z-index:2;width:auto!important;margin:0;padding:.35rem!important;border:0!important;background:transparent!important;color:#176b45!important;cursor:pointer}.bulk-date-trigger::before{content:"";display:block;width:1rem;height:.85rem;border:2px solid currentColor;border-radius:.18rem;background:linear-gradient(to bottom,currentColor 0 .2rem,transparent .2rem)}.bulk-cancel input,.bulk-cancel select,.bulk-cancel textarea{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.65rem;font:inherit;background:#fff}.bulk-cancel textarea{min-height:3.8rem;resize:vertical}.bulk-cancel button{border:0;border-radius:.55rem;padding:.65rem .9rem;font:inherit;font-weight:800;cursor:pointer;background:#a94b1b;color:#fff;justify-self:start}.bulk-preview{border:1px solid #efd7bd;border-radius:.65rem;background:#fffaf4;padding:.7rem;color:#76500b}.bulk-preview strong{color:#a94b1b}@media(max-width:760px){.bulk-cancel-grid{grid-template-columns:1fr}.bulk-cancel button{width:100%}.bulk-roster-submit button{width:auto!important}}</style>
</style><style>.exception-list-filter{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.7rem;align-items:end;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #e2eee7}.exception-list-filter>strong{grid-column:1/-1;color:#17352b}.exception-list-filter label{display:grid;gap:.3rem;margin:0;color:#345b4e;font-size:.8rem}.exception-list-filter input,.exception-list-filter select{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.6rem;font:inherit;background:#fff}.filter-submit{border:0;border-radius:.55rem;padding:.65rem .9rem;background:#176b45;color:#fff;font:inherit;font-weight:800;cursor:pointer}@media(max-width:760px){.exception-list-filter{grid-template-columns:1fr 1fr}.exception-list-filter>strong{grid-column:1/-1}.filter-submit{width:100%}}</style>
</head>
<body>
@php($weekdayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'])
<div class="waka-shell">@include('academic.partials.sidebar', ['activeMenu' => 'attendance'])<main class="waka-content"><header class="waka-topbar"><div><p class="eyebrow">Kontrol kualitas data</p><h1>Perlu Perhatian Kehadiran</h1><p class="muted">Daftar sesi yang belum lengkap atau belum diperiksa.</p></div></header>
    <section class="card"><div class="muted">Pantau sesi yang memiliki data kehadiran belum lengkap atau belum diperiksa. Waka Akademik juga dapat membatalkan beberapa sesi sekaligus bila kegiatan akademik belum dimulai.</div></section>
    <section class="card">
        <form class="exception-list-filter" method="GET" action="{{ route('academic.attendance.exceptions') }}">
            <strong>Fokus daftar temuan</strong>
            <label>Bulan<input type="month" name="list_month" value="{{ $listFilters['list_month'] ?? '' }}"></label>
            <label>Kelas<select name="list_class_id"><option value="">Semua kelas resmi</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected(($listFilters['list_class_id'] ?? '') === $class->id)>{{ $class->display_name }}</option>@endforeach</select></label>
            <label>Urutan<select name="list_sort"><option value="newest" @selected(($listFilters['list_sort'] ?? 'newest') === 'newest')>Terbaru dahulu</option><option value="oldest" @selected(($listFilters['list_sort'] ?? '') === 'oldest')>Terlama dahulu</option></select></label>
            <button class="filter-submit" type="submit">Terapkan</button>
        </form>
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
            <p class="muted" style="margin:0 0 .6rem">Pada layar kecil, geser tabel ke kiri/kanan untuk melihat semua kolom.</p><div class="table-wrap">
                <table>
                    <thead><tr><th>Sesi</th><th>Kelas</th><th>Waktu</th><th>Temuan</th><th>Aksi</th></tr></thead>
                    <tbody>
                    @foreach ($exceptions as $item)
                        @php($session = $item['session'])
                        @php($finding = $item['finding'])
                        <tr>
                            <td><strong>Sesi {{ $session->academicClass?->display_name ?? 'Kelas' }}</strong><br><span class="muted">{{ ['PLANNED' => 'Dijadwalkan', 'CONFIRMED' => 'Dikonfirmasi', 'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan'][$session->session_status] ?? $session->session_status }}</span></td>
                            <td>{{ $session->academicClass?->display_name ?? $session->class_id }}</td>
                            <td><strong>{{ $weekdayNames[$session->planned_start_at->format('l')] ?? $session->planned_start_at->format('l') }}</strong><br><span class="muted">{{ $session->planned_start_at->format('d M Y, H:i') }}</span></td>
                            <td>
                                @if ($finding['no_participants'] ?? false)
                                    <span class="flag">Roster santri belum dibuat</span>
                                @endif
                                @if ($finding['missing_attendance_participant_ids'] !== [])
                                    <span class="flag">{{ count($finding['missing_attendance_participant_ids']) }} belum dibuat</span>
                                @endif
                                @if ($finding['unresolved_attendance_participant_ids'] !== [])
                                    <span class="flag">{{ count($finding['unresolved_attendance_participant_ids']) }} belum tervalidasi</span>
                                @endif
                            </td>
                            <td>@if ($finding['no_participants'] ?? false)<form method="POST" action="{{ route('academic.attendance.snapshot-participants', $session) }}" onsubmit="return window.confirm('Buat snapshot roster santri untuk sesi ini?')">@csrf<button class="attendance-action" type="submit">Buat roster</button></form>@else<a class="attendance-action" href="{{ route('academic.attendance.show', $session) }}">Isi Kehadiran</a>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    <section id="bulk-cancel" class="card bulk-cancel">
        <div><h2 style="margin:.1rem 0 .3rem">Pembatalan Sesi Massal</h2><p class="muted" style="margin:0">Khusus Waka Akademik/Super Admin. Pilih kelas dan rentang tanggal untuk melihat sesi yang aman dibatalkan.</p></div>
        <form class="bulk-cancel-grid" method="GET" action="{{ route('academic.attendance.exceptions') }}#bulk-cancel">
            <label>Kelas<select name="class_id"><option value="">Semua kelas resmi</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(($bulkFormFilters['class_id'] ?? '') === $class->id)>{{ $class->display_name }}</option>@endforeach</select></label>
            <label>Mulai <span class="bulk-date-day" data-date-day="from">{{ !empty($bulkFormFilters['from']) ? ($weekdayNames[\Illuminate\Support\Carbon::parse($bulkFormFilters['from'])->format('l')] ?? '') : '' }}</span><div class="bulk-date-picker"><input class="bulk-date-display" type="text" value="{{ !empty($bulkFormFilters['from']) ? \Illuminate\Support\Carbon::parse($bulkFormFilters['from'])->format('d/m/Y') : '' }}" placeholder="dd/mm/yyyy" readonly aria-hidden="true"><button class="bulk-date-trigger" type="button" aria-label="Buka kalender tanggal mulai"></button><input class="bulk-date-native" lang="id" type="date" name="from" value="{{ $bulkFormFilters['from'] ?? '' }}" required aria-label="Tanggal mulai"></div></label>
            <label>Sampai <span class="bulk-date-day" data-date-day="to">{{ !empty($bulkFormFilters['to']) ? ($weekdayNames[\Illuminate\Support\Carbon::parse($bulkFormFilters['to'])->format('l')] ?? '') : '' }}</span><div class="bulk-date-picker"><input class="bulk-date-display" type="text" value="{{ !empty($bulkFormFilters['to']) ? \Illuminate\Support\Carbon::parse($bulkFormFilters['to'])->format('d/m/Y') : '' }}" placeholder="dd/mm/yyyy" readonly aria-hidden="true"><button class="bulk-date-trigger" type="button" aria-label="Buka kalender tanggal selesai"></button><input class="bulk-date-native" lang="id" type="date" name="to" value="{{ $bulkFormFilters['to'] ?? '' }}" required aria-label="Tanggal selesai"></div></label>
            <button type="submit" style="background:#176b45">Pratinjau</button>
        </form>
        @if (($filters['from'] ?? null) && ($filters['to'] ?? null))
            <div class="bulk-preview"><strong>{{ $bulkSessions->count() }} sesi</strong> memenuhi syarat: status terjadwal, kelas resmi, dan belum memiliki data kehadiran.</div>
            @if ($bulkSessions->isNotEmpty())
                <form class="bulk-cancel-submit" method="POST" action="{{ route('academic.attendance.exceptions.bulk-cancel') }}" onsubmit="return window.confirm('Batalkan {{ $bulkSessions->count() }} sesi yang tampil dalam pratinjau?')">
                    @csrf<input type="hidden" name="class_id" value="{{ $filters['class_id'] ?? '' }}"><input type="hidden" name="from" value="{{ $filters['from'] }}"><input type="hidden" name="to" value="{{ $filters['to'] }}">
                    <label>Alasan pembatalan<textarea name="reason" required maxlength="1000" placeholder="Contoh: KBM baru dimulai 7 Juli 2026">{{ old('reason') }}</textarea></label>
                    <button type="submit">Batalkan {{ $bulkSessions->count() }} sesi</button>
                </form>
            @endif
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
