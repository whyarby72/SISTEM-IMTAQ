<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Kelas</title><style>@include('admin.partials.styles')
    .edit-form .form-submit{margin-top:1.25rem}
</style></head>
<body><main style="max-width:52rem"><section class="card"><p class="eyebrow">Data induk</p><h1>Edit Kelas</h1><p><a class="back" href="{{ route('admin.academic.classes.index') }}">Kembali ke data kelas</a></p>
    <p class="muted">Identitas kode dan struktur kelas dikunci untuk menjaga histori.</p>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="edit-form" method="post" action="{{ route('admin.academic.classes.update', $class) }}">@csrf @method('PUT')
        <label>Kode kelas<input class="readonly" value="{{ $class->class_code }}" readonly></label>
        <label>Nama kelas<input name="display_name" value="{{ old('display_name', $class->display_name) }}" required></label>
        <label>Status<select name="status" required><option value="ACTIVE" @selected(old('status', $class->status) === 'ACTIVE')>Aktif</option><option value="INACTIVE" @selected(old('status', $class->status) === 'INACTIVE')>Tidak aktif</option></select></label>
        <button class="button form-submit" type="submit">Simpan perubahan</button>
    </form></section></main></body></html>
