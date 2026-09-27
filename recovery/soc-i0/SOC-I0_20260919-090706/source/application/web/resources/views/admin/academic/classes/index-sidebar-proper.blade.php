<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Kelas</title>
    <style>
        @include('academic.partials.sidebar-styles')
        .class-table { min-width:52rem; }
        .class-table th, .class-table td { padding:.85rem .7rem; }
    </style>
</head>
<body><div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'classes'])
    <main class="waka-content">
        <header class="waka-topbar"><div><p class="eyebrow">Data induk</p><h1>Data Kelas</h1><p class="muted">Kelola kelas resmi untuk penjadwalan dan roster santri.</p></div></header>
        <section class="card"><div class="actions"><div><h2 style="margin:0">Kelas aktif</h2><p class="muted" style="margin:.25rem 0 0">Data pilot lama tetap tersimpan sebagai histori.</p></div><a class="button" href="{{ route('admin.academic.classes.create') }}">+ Tambah kelas</a></div>
            @if ($currentAcademicYear)<p class="notice" style="margin:1rem 0 0">Menampilkan kelas resmi {{ $currentAcademicYear->display_name }}.</p>@endif
        </section>
        @if (session('status'))<p class="notice">{{ session('status') }}</p>@endif
        <section class="card"><p class="muted">Pada layar kecil, geser tabel ke kiri/kanan untuk melihat semua kolom.</p><div class="table-wrap"><table class="class-table"><thead><tr><th>Kode</th><th>Nama</th><th>Tahun ajaran</th><th>Tingkat</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
            @forelse ($classes as $class)
                <tr><td>{{ $class->class_code }}</td><td>@uiLabel($class->display_name)</td><td>@uiLabel($class->academicYear->display_name)</td><td>@uiLabel($class->gradeLevel->display_name)</td><td>{{ $class->status === 'ACTIVE' ? 'Aktif' : 'Tidak aktif' }}</td><td><a href="{{ route('admin.academic.classes.edit', $class) }}">Edit</a></td></tr>
            @empty
                <tr><td colspan="6" class="muted">Belum ada kelas.</td></tr>
            @endforelse
        </tbody></table></div>{{ $classes->links() }}</section>
    </main>
</div></body></html>
