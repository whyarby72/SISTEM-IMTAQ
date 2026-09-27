<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tambah Guru</title><style>@include('academic.partials.sidebar-styles')</style></head>
<body><div class="waka-shell">@include('academic.partials.sidebar', ['activeMenu' => 'staff'])<main class="waka-content"><section class="card" style="max-width:70rem"><p class="eyebrow">Data induk</p><h1>Tambah Guru/Staf</h1><p><a class="back" href="{{ route('admin.academic.staff.index') }}">Kembali ke master guru</a></p>
    @if ($errors->any())<div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="post" action="{{ route('admin.academic.staff.store') }}">@csrf
        <label>Kode staf<input name="staff_code" value="{{ old('staff_code') }}" required></label>
        <label>Nama lengkap<input name="full_name" value="{{ old('full_name') }}" required></label>
        <label>Mulai bertugas<input lang="id" type="date" name="active_from" value="{{ old('active_from') }}"></label>
        <label>Selesai bertugas<input lang="id" type="date" name="active_until" value="{{ old('active_until') }}"></label>
        <button class="button" type="submit">Simpan guru/staf</button>
    </form></section></main></div></body></html>
