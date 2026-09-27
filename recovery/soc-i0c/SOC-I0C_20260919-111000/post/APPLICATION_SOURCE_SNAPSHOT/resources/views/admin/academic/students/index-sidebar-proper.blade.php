<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Santri</title>
    <style>
        @include('academic.partials.sidebar-styles')
        .student-filter { display:grid; grid-template-columns:minmax(14rem,1fr) minmax(12rem,.7fr) auto auto; gap:.8rem; align-items:end; }
        .student-filter label { margin:0; }
        .student-table { min-width:72rem; }
        .student-table input { min-width:9rem; margin-top:0; }
        .student-table td:nth-child(2) { direction:rtl; text-align:right; font-size:1.05rem; }
        .student-table .button { white-space:nowrap; }
        @media (max-width:760px) { .student-filter { grid-template-columns:1fr; } .student-table { min-width:72rem; } }
    </style>
</head>
<body>
<div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'students'])
    <main class="waka-content">
        <header class="waka-topbar">
            <div>
                <p class="eyebrow">Data induk</p>
                <h1>Data Santri</h1>
                <p class="muted">Kelola identitas santri dan kelas aktifnya.</p>
            </div>
        </header>

        <section class="card">
            <div class="actions">
                <div>
                    <h2 style="margin:0">Daftar santri</h2>
                    <p class="muted" style="margin:.25rem 0 0">NIS/NISN dapat dilengkapi kemudian.</p>
                </div>
                <a class="button" href="{{ route('admin.academic.students.create') }}">+ Tambah santri</a>
            </div>
            @if ($currentAcademicYear)
                <p class="notice" style="margin:1rem 0 0">Menampilkan roster {{ $currentAcademicYear->display_name }}. Data pilot lama tetap tersimpan sebagai histori.</p>
            @endif
        </section>

        <section class="card">
            @if (session('status'))
                <p class="notice">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <div class="error"><ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul></div>
            @endif

            <form class="student-filter" method="GET" action="{{ route('admin.academic.students.index') }}">
                <label>Cari nama<input name="q" value="{{ $search }}" placeholder="Nama Indonesia atau Arab"></label>
                <label>Kelas<select name="class_id">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected($classId === $class->id)>@uiLabel($class->display_name)</option>
                    @endforeach
                </select></label>
                <button class="button" type="submit">Terapkan</button>
                @if ($search !== '' || $classId)
                    <a class="button secondary" href="{{ route('admin.academic.students.index') }}">Reset</a>
                @endif
            </form>

            <p class="muted" style="margin:1.25rem 0 .6rem">Menampilkan <strong>{{ $students->total() }}</strong> santri.</p>
            <div class="table-wrap">
                <table class="student-table">
                    <thead><tr><th>Nama Indonesia</th><th>Nama Arab</th><th>Kelas aktif</th><th>Tahun masuk</th><th>NIS</th><th>NISN</th><th>Aksi</th></tr></thead>
                    <tbody>
                    @forelse ($students as $student)
                        @php
                            $enrollment = $student->classEnrollments->firstWhere('status', 'ACTIVE');
                        @endphp
                        <tr>
                            <td><strong>{{ $student->full_name }}</strong><br><a href="{{ route('admin.academic.students.edit', $student) }}">Edit data</a></td>
                            <td>{{ $student->arabic_name }}</td>
                            <td>@uiLabel($enrollment?->academicClass?->display_name ?? 'Belum ditetapkan')</td>
                            <td>{{ $student->entry_year ?? '—' }}</td>
                            <td><input form="student-{{ $student->id }}" name="nis" value="{{ $student->nis }}" maxlength="50" aria-label="NIS {{ $student->full_name }}" placeholder="Belum diisi"></td>
                            <td><input form="student-{{ $student->id }}" name="nisn" value="{{ $student->nisn }}" maxlength="20" aria-label="NISN {{ $student->full_name }}" placeholder="Belum diisi"></td>
                            <td><form id="student-{{ $student->id }}" method="POST" action="{{ route('admin.academic.students.update', $student) }}">@csrf @method('PUT')<button class="button" type="submit">Simpan</button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">Tidak ada santri yang sesuai filter.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $students->links() }}
        </section>
    </main>
</div>
</body>
</html>
