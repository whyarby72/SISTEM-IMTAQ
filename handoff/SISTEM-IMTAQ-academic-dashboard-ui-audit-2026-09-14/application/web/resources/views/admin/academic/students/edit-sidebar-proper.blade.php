<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Santri</title>
    <style>
        @include('academic.partials.sidebar-styles')
        .student-form { max-width:52rem; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .form-grid label { margin:0; }
        .form-actions { display:flex; gap:.75rem; flex-wrap:wrap; margin-top:1.25rem; }
        @media (max-width:680px) { .form-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'students'])
    <main class="waka-content">
        <header class="waka-topbar">
            <div>
                <p class="eyebrow">Data induk</p>
                <h1>Edit Santri</h1>
                <p class="muted">Perbarui identitas; perubahan kelas disimpan sebagai riwayat baru.</p>
            </div>
        </header>
        <section class="card student-form">
            <p><a class="back" href="{{ route('admin.academic.students.index') }}">Kembali ke daftar santri</a></p>
            <p class="notice">ID internal tetap. Perubahan kelas berlaku mulai tanggal yang dipilih dan tidak mengubah histori sebelumnya.</p>
            @if ($errors->any())
                <div class="error"><ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul></div>
            @endif
            @php
                $currentEnrollment = $student->classEnrollments->firstWhere('status', 'ACTIVE');
            @endphp
            <form method="post" action="{{ route('admin.academic.students.update', $student) }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <label>Nama Indonesia<input name="full_name" value="{{ old('full_name', $student->full_name) }}" required></label>
                    <label>Nama Arab<input name="arabic_name" value="{{ old('arabic_name', $student->arabic_name) }}"></label>
                    <label>Tahun masuk<input type="number" name="entry_year" value="{{ old('entry_year', $student->entry_year) }}" min="2000" max="2100"></label>
                    <label>Kelas aktif<select name="class_id" required><option value="">Pilih kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('class_id', $currentEnrollment?->class_id) === $class->id)>@uiLabel($class->display_name)</option>
                        @endforeach
                    </select></label>
                    <label>Berlaku mulai<input type="date" name="class_effective_from" value="{{ old('class_effective_from', now()->toDateString()) }}" required></label>
                    <label>NIS<input name="nis" value="{{ old('nis', $student->nis) }}" maxlength="50"></label>
                    <label>NISN<input name="nisn" value="{{ old('nisn', $student->nisn) }}" maxlength="20"></label>
                </div>
                <div class="form-actions">
                    <button class="button" type="submit">Simpan perubahan</button>
                    <a class="button secondary" href="{{ route('admin.academic.students.index') }}">Batal</a>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
