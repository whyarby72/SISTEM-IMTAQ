<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tambah Kelas</title><style>@include('admin.partials.styles')</style></head>
<body><main style="max-width:52rem"><section class="card"><p class="eyebrow">Data induk</p><h1>Tambah Kelas</h1><p><a class="back" href="{{ route('admin.academic.classes.index') }}">Kembali ke data kelas</a></p>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="post" action="{{ route('admin.academic.classes.store') }}">@csrf
        <label>Kode kelas<input name="class_code" value="{{ old('class_code') }}" required></label>
        <label>Nama kelas<input name="display_name" value="{{ old('display_name') }}" required></label>
        <label>Rombel/Bagian<input name="section_code" value="{{ old('section_code') }}" required></label>
        <label>Tahun ajaran<select name="academic_year_id" required><option value="">Pilih</option>@foreach ($academicYears as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id') === $year->id)>@uiLabel($year->display_name)</option>@endforeach</select></label>
        <label>Unit organisasi<select name="organizational_unit_id" required><option value="">Pilih</option>@foreach ($organizationalUnits as $unit)<option value="{{ $unit->id }}" @selected(old('organizational_unit_id') === $unit->id)>{{ $unit->unit_name }}</option>@endforeach</select></label>
        <label>Tingkat<select name="grade_level_id" required><option value="">Pilih</option>@foreach ($gradeLevels as $level)<option value="{{ $level->id }}" @selected(old('grade_level_id') === $level->id)>@uiLabel($level->display_name)</option>@endforeach</select></label>
        <button class="button" type="submit">Simpan kelas</button>
    </form></section></main></body></html>
