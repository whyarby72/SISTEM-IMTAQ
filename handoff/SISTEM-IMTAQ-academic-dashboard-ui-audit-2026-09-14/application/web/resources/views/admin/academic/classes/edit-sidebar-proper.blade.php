<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit Kelas</title><style>@include('academic.partials.sidebar-styles') .class-form{max-width:52rem}.form-actions{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.25rem}</style></head>
<body><div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'classes'])
    <main class="waka-content"><header class="waka-topbar"><div><p class="eyebrow">Data induk</p><h1>Edit Kelas</h1><p class="muted">Perbarui nama atau status tanpa mengubah identitas histori.</p></div></header>
        <section class="card class-form"><p><a class="back" href="{{ route('admin.academic.classes.index') }}">Kembali ke data kelas</a></p><p class="notice">Identitas kode dan struktur kelas dikunci untuk menjaga histori akademik.</p>
            @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="post" action="{{ route('admin.academic.classes.update', $class) }}">@csrf @method('PUT')<label>Kode kelas<input value="{{ $class->class_code }}" readonly></label><label>Nama kelas<input name="display_name" value="{{ old('display_name', $class->display_name) }}" required></label><label>Status<select name="status" required><option value="ACTIVE" @selected(old('status', $class->status) === 'ACTIVE')>Aktif</option><option value="INACTIVE" @selected(old('status', $class->status) === 'INACTIVE')>Tidak aktif</option></select></label><div class="form-actions"><button class="button" type="submit">Simpan perubahan</button><a class="button secondary" href="{{ route('admin.academic.classes.index') }}">Batal</a></div></form>
        </section></main>
</div></body></html>
