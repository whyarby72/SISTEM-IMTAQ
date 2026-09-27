<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Guru</title><style>@include('academic.partials.sidebar-styles')</style></head>
<body><div class="waka-shell">@include('academic.partials.sidebar', ['activeMenu' => 'staff'])<main class="waka-content"><section class="card" style="max-width:70rem"><p class="eyebrow">Data induk</p><h1>Edit Guru/Staf</h1><p><a class="back" href="{{ route('admin.academic.staff.index') }}">Kembali ke master guru</a></p><p class="muted">Kode staf dikunci untuk menjaga identitas utama.</p>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="post" action="{{ route('admin.academic.staff.update', $staff) }}">@csrf @method('PUT')
        <label>Kode staf<input class="readonly" value="{{ $staff->staff_code }}" readonly></label>
        <label>Nama lengkap<input name="full_name" value="{{ old('full_name', $staff->full_name) }}" required></label>
        <label>Status<select name="record_status" required><option value="ACTIVE" @selected(old('record_status', $staff->record_status) === 'ACTIVE')>Aktif</option><option value="INACTIVE" @selected(old('record_status', $staff->record_status) === 'INACTIVE')>Tidak aktif</option></select></label>
        <label>Mulai bertugas<input lang="id" type="date" name="active_from" value="{{ old('active_from', $staff->active_from?->format('Y-m-d')) }}"></label>
        <label>Selesai bertugas<input lang="id" type="date" name="active_until" value="{{ old('active_until', $staff->active_until?->format('Y-m-d')) }}"></label>
        <button class="button" type="submit">Simpan perubahan</button>
    </form></section></main></div></body></html>
