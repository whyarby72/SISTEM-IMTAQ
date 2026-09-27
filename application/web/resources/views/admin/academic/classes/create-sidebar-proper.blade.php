<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tambah Kelas</title><style>@include('academic.partials.sidebar-styles') .class-form{max-width:58rem}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.form-grid label{margin:0}.form-actions{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.25rem}@media(max-width:680px){.form-grid{grid-template-columns:1fr}}</style></head>
<body><div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'classes'])
    <main class="waka-content"><header class="waka-topbar"><div><p class="eyebrow">Data induk</p><h1>Tambah Kelas</h1><p class="muted">Siapkan kelas resmi untuk alur akademik.</p></div></header>
        <section class="card class-form"><p><a class="back" href="{{ route('admin.academic.classes.index') }}">Kembali ke data kelas</a></p>
            @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="post" action="{{ route('admin.academic.classes.store') }}">@csrf<div class="form-grid">
                <label>Kode kelas<input name="class_code" value="{{ old('class_code') }}" required></label><label>Nama kelas<input name="display_name" value="{{ old('display_name') }}" required></label><label>Rombel/Bagian<input name="section_code" value="{{ old('section_code') }}" required></label>
                <label>Tahun ajaran<select name="academic_year_id" required><option value="">Pilih tahun ajaran</option>@foreach ($academicYears as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id') === $year->id)>@uiLabel($year->display_name)</option>@endforeach</select></label>
                <label>Unit organisasi<select name="organizational_unit_id" required><option value="">Pilih unit</option>@foreach ($organizationalUnits as $unit)<option value="{{ $unit->id }}" @selected(old('organizational_unit_id') === $unit->id)>{{ $unit->unit_name }}</option>@endforeach</select></label>
                <label>Tingkat<select name="grade_level_id" required><option value="">Pilih tingkat</option>@foreach ($gradeLevels as $level)<option value="{{ $level->id }}" @selected(old('grade_level_id') === $level->id)>@uiLabel($level->display_name)</option>@endforeach</select></label>
            </div><div class="form-actions"><button class="button" type="submit">Simpan kelas</button><a class="button secondary" href="{{ route('admin.academic.classes.index') }}">Batal</a></div></form>
        </section></main>
</div></body></html>
