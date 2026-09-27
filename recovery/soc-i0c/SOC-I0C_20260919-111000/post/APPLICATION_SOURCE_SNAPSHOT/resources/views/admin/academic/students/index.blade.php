<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Data Santri</title>
    <style>@include('admin.partials.styles')
        .student-table{min-width:76rem}.student-table th,.student-table td{padding:1rem .85rem}.student-table th:nth-child(1){min-width:14rem}.student-table th:nth-child(2){min-width:13rem}.student-table th:nth-child(3){min-width:8rem}.student-table th:nth-child(4){min-width:7rem}.student-table th:nth-child(5),.student-table th:nth-child(6){min-width:11rem}.student-table th:last-child{min-width:7rem}.student-table td:nth-child(2){direction:rtl;text-align:right;font-size:1.05rem;line-height:1.7}.student-table input{min-width:9.5rem;margin-top:0}.student-table .button{white-space:nowrap}.filter-bar{display:grid;grid-template-columns:minmax(16rem,1fr) minmax(12rem,.7fr) auto auto;gap:1rem;align-items:end}.filter-bar label{margin-top:0}.filter-bar .button{margin-bottom:0}@media(max-width:760px){.filter-bar{grid-template-columns:1fr}.student-table{min-width:72rem}}
    </style>
</head>
<body><main>
    <header class="topbar"><a class="brand" href="{{ route('admin.academic.dashboard') }}">SISTEM IMTAQ <small>Waka Akademik</small></a><nav class="nav"><a href="{{ route('admin.academic.dashboard') }}">Ringkasan</a><a class="active" href="{{ route('admin.academic.students.index') }}">Santri</a><a href="{{ route('admin.academic.classes.index') }}">Kelas</a><a href="{{ route('admin.academic.staff.index') }}">Guru/Staf</a><a href="{{ route('admin.academic.schedules.index') }}">Jadwal</a></nav></header>
    <section class="card"><p class="eyebrow">Data induk · Roster Juli 2026</p><div class="actions"><div><h1>Data Santri</h1><p class="muted">Daftar 84 santri roster historis dan kelas aktifnya. NIS/NISN dapat dilengkapi kemudian.</p></div><a class="button" href="{{ route('admin.academic.students.create') }}">+ Tambah santri</a></div>@if ($currentAcademicYear)<p class="notice" style="margin:1rem 0 0">Roster 84 santri tetap ditampilkan. Pemetaan enrollment dari kelas pilot ke kelas resmi belum dilakukan, sehingga data santri tidak disembunyikan.</p>@endif</section>
    <section class="card">
        @if (session('status')) <p class="notice">{{ session('status') }}</p> @endif
        @if ($errors->any()) <div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
        <form class="filter-bar" method="GET" action="{{ route('admin.academic.students.index') }}">
            <label>Cari nama<input name="q" value="{{ $search }}" placeholder="Nama Indonesia atau Arab"></label>
            <label>Kelas<select name="class_id"><option value="">Semua kelas</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected($classId === $class->id)>@uiLabel($class->display_name)</option>@endforeach</select></label>
            <button class="button" type="submit">Terapkan</button>
            @if ($search !== '' || $classId) <a class="button secondary" href="{{ route('admin.academic.students.index') }}">Reset</a> @endif
        </form>
        <p class="muted" style="margin:1.25rem 0 .6rem">Menampilkan <strong>{{ $students->total() }}</strong> santri. Isi NIS/NISN jika sudah tersedia, lalu tekan Simpan.</p>
        <div class="table-wrap"><table class="student-table"><thead><tr><th>Nama Indonesia</th><th>Nama Arab</th><th>Kelas aktif</th><th>Tahun masuk</th><th>NIS</th><th>NISN</th><th>Aksi</th></tr></thead><tbody>
        @forelse ($students as $student)
            @php($enrollment = $student->classEnrollments->firstWhere('status', 'ACTIVE'))
            <tr><td><strong>{{ $student->full_name }}</strong><br><a href="{{ route('admin.academic.students.edit', $student) }}">Edit data</a></td><td>{{ $student->arabic_name }}</td><td>@uiLabel($enrollment?->academicClass?->display_name ?? 'Belum ditetapkan')</td><td>{{ $student->entry_year ?? '—' }}</td><td><input form="student-{{ $student->id }}" name="nis" value="{{ $student->nis }}" maxlength="50" aria-label="NIS {{ $student->full_name }}" placeholder="Belum diisi"></td><td><input form="student-{{ $student->id }}" name="nisn" value="{{ $student->nisn }}" maxlength="20" aria-label="NISN {{ $student->full_name }}" placeholder="Belum diisi"></td><td><form id="student-{{ $student->id }}" method="POST" action="{{ route('admin.academic.students.update', $student) }}">@csrf @method('PUT')<button class="button" type="submit">Simpan</button></form></td></tr>
        @empty
            <tr><td colspan="7" class="muted">Tidak ada santri yang sesuai filter.</td></tr>
        @endforelse
        </tbody></table></div>{{ $students->links() }}
    </section>
</main><form method="POST" action="{{ route('logout') }}" style="max-width:78rem;margin:0 auto 1rem;text-align:right">@csrf<button class="button" type="submit">Keluar ({{ auth()->user()->roles->pluck('name')->join(' · ') }})</button></form></body></html>
