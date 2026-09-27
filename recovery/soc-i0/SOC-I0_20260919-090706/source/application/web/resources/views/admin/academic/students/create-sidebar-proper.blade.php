<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Santri</title>
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
                <h1>Tambah Santri</h1>
                <p class="muted">Buat identitas santri dan tetapkan kelas aktifnya.</p>
            </div>
        </header>
        <section class="card student-form">
            <p><a class="back" href="{{ route('admin.academic.students.index') }}">Kembali ke daftar santri</a></p>
            <p class="notice">ID internal dibuat otomatis oleh sistem. NIS/NISN boleh dilengkapi kemudian.</p>
            @if ($errors->any())
                <div class="error"><ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul></div>
            @endif
            <form method="post" action="{{ route('admin.academic.students.store') }}">
                @csrf
                <div class="form-grid">
                    <label>Nama Indonesia<input name="full_name" value="{{ old('full_name') }}" required></label>
                    <label>Nama Arab<input name="arabic_name" value="{{ old('arabic_name') }}"></label>
                    <label>Tahun masuk<input type="number" name="entry_year" value="{{ old('entry_year') }}" min="2000" max="2100" placeholder="Contoh: 2026"></label>
                    <label>Kelas aktif<select name="class_id" required><option value="">Pilih kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('class_id') === $class->id)>@uiLabel($class->display_name)</option>
                        @endforeach
                    </select></label>
                    <label>NIS<input name="nis" value="{{ old('nis') }}" maxlength="50" placeholder="Boleh dikosongkan"></label>
                    <label>NISN<input name="nisn" value="{{ old('nisn') }}" maxlength="20" placeholder="Boleh dikosongkan"></label>
                </div>
                <div class="form-actions">
                    <button class="button" type="submit">Simpan santri</button>
                    <a class="button secondary" href="{{ route('admin.academic.students.index') }}">Batal</a>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
