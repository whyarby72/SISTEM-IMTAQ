<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tingkat & Wali Kelas</title>
    <style>
        @include('academic.partials.sidebar-styles')
        .structure-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.structure-grid label{margin:0;min-width:0}.structure-grid input,.structure-grid select{min-width:0}.structure-card form>.button{margin-top:1rem}.table-wrap{max-width:100%;overflow-x:auto}.structure-card h2{margin-top:0}
        @media(max-width:680px){.structure-grid{grid-template-columns:1fr}}
    </style>
</head>
<body><div class="waka-shell">
@include('academic.partials.sidebar',['activeMenu'=>'structure'])
<main class="waka-content"><header class="waka-topbar"><div><p class="eyebrow">Data induk akademik</p><h1>Tingkat & Wali Kelas</h1><p class="muted">Siapkan tingkat pendidikan dan tetapkan Wali Kelas.</p></div></header>
@if($currentAcademicYear)<p class="notice">Daftar kelas menggunakan kelas resmi {{ $currentAcademicYear->display_name }}.</p>@endif
@if(session('status'))<p class="notice">{{ session('status') }}</p>@endif
@if($errors->any())<div class="error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="card structure-card"><h2>Tambah tingkat</h2><form method="post" action="{{ route('admin.academic.structure.grade-levels.store') }}"><div class="structure-grid">@csrf<label>Unit organisasi<select name="organizational_unit_id" required><option value="">Pilih unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}">@uiLabel($unit->unit_name)</option>@endforeach</select></label><label>Kode tingkat<input name="level_code" placeholder="1" required></label><label>Nama tingkat<input name="display_name" placeholder="Tingkat 1" required></label><label>Urutan<input type="number" name="sequence_no" min="1" required></label></div><button class="button" type="submit">Simpan tingkat</button></form></section>
<section class="card structure-card"><h2>Tambah Wali Kelas</h2><p class="muted">Pilih kelas dan guru/staf resmi untuk penetapan baru.</p><form method="post" action="{{ route('admin.academic.structure.homerooms.store') }}"><div class="structure-grid">@csrf<label>Kelas<select name="class_id" required><option value="">Pilih kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}">@uiLabel($class->display_name)</option>@endforeach</select></label><label>Wali Kelas<select name="staff_id" required><option value="">Pilih guru/staf</option>@foreach($staff as $person)<option value="{{ $person->id }}">{{ $person->full_name }}</option>@endforeach</select></label><label>Mulai bertugas<input type="date" name="effective_from" lang="id" required></label><label>Selesai bertugas<input type="date" name="effective_until" lang="id"></label><label>Alasan penetapan<input name="reason" value="MASTER-DATA-ADMIN" required></label></div><button class="button" type="submit">Tetapkan Wali Kelas</button></form></section>
<section class="card structure-card"><h2>Wali Kelas aktif</h2><div class="table-wrap"><table><thead><tr><th>Kelas</th><th>Wali Kelas</th><th>Mulai</th><th>Selesai</th><th>Aksi</th></tr></thead><tbody>@forelse($classes as $class) @foreach($class->homeroomAssignments->where('status','ACTIVE') as $assignment)<tr><td>@uiLabel($class->display_name)</td><td>{{ $assignment->staff->full_name }}</td><td>{{ $assignment->effective_from->format('d-m-Y') }}</td><td>{{ $assignment->effective_until?->format('d-m-Y') ?? 'Masih aktif' }}</td><td><a href="{{ route('admin.academic.structure.homerooms.edit',$assignment) }}">Edit</a></td></tr>@endforeach @empty<tr><td colspan="5" class="muted">Belum ada penetapan.</td></tr>@endforelse</tbody></table></div></section>
</main></div></body></html>
