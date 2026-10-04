<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nilai Semester</title>
    <style>
        @include('academic.partials.sidebar-styles')
        .grade-page{max-width:1180px;margin:0 auto}.grade-filter{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8rem;background:#fff;border:1px solid #dceae2;border-radius:1rem;padding:1rem;margin-bottom:1rem}.grade-filter label{display:grid;gap:.35rem;color:#527066;font-size:.76rem;font-weight:800}.grade-filter select{width:100%;min-height:2.6rem;border:1px solid #bfd3c7;border-radius:.55rem;padding:.55rem;font:inherit;background:#fff;color:#17352b}.grade-filter button{align-self:end;min-height:2.6rem;border:0;border-radius:.55rem;padding:.55rem 1rem;font:inherit;font-weight:800;background:#176b4d;color:#fff;cursor:pointer}.grade-context{display:flex;flex-wrap:wrap;gap:.45rem;margin-bottom:1rem;color:#527066;font-size:.8rem}.grade-context span{border:1px solid #cfe1d7;border-radius:999px;background:#f3f8f5;padding:.3rem .55rem}.grade-card{background:#fff;border:1px solid #dceae2;border-radius:1rem;padding:1rem;box-shadow:0 8px 24px rgba(16,72,51,.05)}.grade-card h2{margin:0;font-size:1.1rem}.grade-card p{color:#6a8278;margin:.3rem 0 1rem}.grade-table-wrap{overflow-x:auto}.grade-table{width:100%;border-collapse:collapse;min-width:48rem}.grade-table th,.grade-table td{padding:.75rem .65rem;text-align:left;border-top:1px solid #e5eee9;vertical-align:middle}.grade-table th{color:#527066;font-size:.7rem;letter-spacing:.05em;text-transform:uppercase}.grade-table td{font-size:.85rem}.grade-table small{display:block;color:#6a8278;margin-top:.2rem}.grade-score{font-weight:900;color:#176b4d}.grade-missing{display:inline-flex;border-radius:999px;padding:.25rem .5rem;background:#fff5df;color:#8a5a08;font-size:.72rem;font-weight:800}.grade-status{color:#527066;font-size:.78rem}.grade-empty{border:1px dashed #cbd9d1;border-radius:.75rem;background:#f7faf8;padding:1.2rem;color:#6a8278}.grade-empty strong{display:block;color:#17352b;margin-bottom:.25rem}@media(max-width:720px){.grade-filter{grid-template-columns:1fr}.grade-filter button{width:100%}.grade-page .waka-topbar{align-items:flex-start;flex-direction:column}}
    </style>
</head>
<body>
<div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'grades'])
    <main class="waka-content">
        <div class="grade-page">
            <header class="waka-topbar"><div><p class="waka-kicker">Academic</p><h1 class="waka-title">Nilai Semester</h1><p class="waka-footnote">Workspace baca untuk nilai mata pelajaran berdasarkan semester dan kelas yang berwenang.</p></div></header>
            <form class="grade-filter" method="GET" action="{{ route('academic.grades.index') }}">
                <label for="semester_id">Semester
                    <select id="semester_id" name="semester_id" onchange="this.form.class_id.value='';this.form.subject_id.value='';this.form.submit()">
                        <option value="">Pilih semester</option>
                        @foreach($semesters as $option)<option value="{{ $option->id }}" @selected($semester?->id === $option->id)>{{ $option->display_name }}</option>@endforeach
                    </select>
                </label>
                <label for="class_id">Kelas
                    <select id="class_id" name="class_id" onchange="this.form.subject_id.value='';this.form.submit()" @disabled($semester === null)>
                        <option value="">Pilih kelas</option>
                        @foreach($classes as $option)<option value="{{ $option->id }}" @selected($class?->id === $option->id)>{{ $option->display_name }}</option>@endforeach
                    </select>
                </label>
                <label for="subject_id">Mata pelajaran
                    <select id="subject_id" name="subject_id" onchange="this.form.submit()" @disabled($class === null)>
                        <option value="">Pilih mata pelajaran</option>
                        @foreach($subjects as $option)<option value="{{ $option->id }}" @selected($subject?->id === $option->id)>{{ $option->subject_name }}</option>@endforeach
                    </select>
                </label>
            </form>
            @if($gradeWorkspace['scope_type'] ?? null)
                <div class="grade-context" aria-label="Konteks otorisasi"><span>Cakupan: {{ $gradeWorkspace['scope_type'] === 'SUBJECT_TEACHER' ? 'Guru mata pelajaran' : ($gradeWorkspace['scope_type'] === 'WALI_KELAS' ? 'Wali kelas' : 'Waka Akademik') }}</span><span>{{ $semester->display_name }}</span><span>{{ $class->display_name }}</span><span>{{ $subject->subject_name }}</span></div>
            @endif
            <section class="grade-card" aria-labelledby="grade-worklist-title">
                @if($semester && $class && $subject)
                    <h2 id="grade-worklist-title">Daftar nilai {{ $subject->subject_name }}</h2>
                    <p>{{ $class->display_name }} · {{ $semester->display_name }} · {{ count($gradeWorkspace['students']) }} santri terdaftar</p>
                    @if(count($gradeWorkspace['students']) > 0)
                        <div class="grade-table-wrap"><table class="grade-table"><caption class="sr-only">Daftar nilai semester, mode baca</caption><thead><tr><th scope="col">Santri</th><th scope="col">Nilai</th><th scope="col">Status</th><th scope="col">Versi</th></tr></thead><tbody>
                        @foreach($gradeWorkspace['students'] as $row)<tr><td><strong>{{ $row['student_code'] }}</strong><small>{{ $row['student_name'] }}</small></td><td>@if($row['is_missing'])<span class="grade-missing">Belum diisi</span>@else<span class="grade-score">{{ $row['score'] }}</span>@endif</td><td><span class="grade-status">{{ $row['workflow_status'] ?? 'Belum ada nilai' }}</span></td><td>{{ $row['version_no'] ?? '—' }}</td></tr>@endforeach
                        </tbody></table></div>
                    @else
                        <div class="grade-empty"><strong>Tidak ada santri pada cakupan ini.</strong>Periksa kembali semester, kelas, dan enrollment aktif.</div>
                    @endif
                @else
                    <div class="grade-empty"><strong>Pilih semester, kelas, dan mata pelajaran.</strong>Daftar pilihan hanya menampilkan cakupan yang diizinkan untuk akun ini.</div>
                @endif
            </section>
        </div>
    </main>
</div>
</body>
</html>
