# Work Log

## 2026-10-04 — Academic Wali dashboard maturity R2B-A joint scope authorization

- Added `SessionAttendanceScopeResolver` for canonical ClassSession scope,
  effective Wali homeroom intersection, effective-date participant mapping,
  and fail-closed unmapped/ambiguous joint sessions.
- Allowed non-anchor Wali access to the same physical joint session while
  exposing only the authorized class partition; Waka/Super Admin retain full
  session visibility. Wali joint finalization remains blocked until the
  separately authorized partitioned-finalization task; `StudentAttendanceFinalizer`
  was not changed.
- Added focused joint partition, unrelated-Wali, unmapped, and ambiguous
  mapping tests; ordinary attendance paths remain in the regression suite.
- PHP lint, Pint, Blade cache, project structure, and diff checks passed.
  Local PHPUnit remained safely blocked by the protected PILOT identity guard;
  no PILOT query/write occurred.
- Decision pending exact disposable PostgreSQL CI; next task after PASS is
  `ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-B-PARTITIONED-FINALIZATION`.

## 2026-10-04 — Academic Wali dashboard maturity R1 operational home

- Reframed the Wali dashboard around own-class identity, urgent work, every
  current-day session, next session, and lower-priority period analytics.
- Added deterministic five-state session classification, unfinished-first
  ordering, canonical attendance CTAs, missing-versus-absent semantics,
  separate teacher status, and safe joint-class participant partitioning.
- Added focused tests for ordering, >12 current-day sessions, GET read-only
  behavior, accessibility/mobile markup, and no-assignment empty state.
- Local PHPUnit remained fail-closed on protected PILOT `imtaq`; no database
  access/write occurred. Disposable PostgreSQL CI run `37204858961` passed on
  executable HEAD `a857e341e88dc195add3f14a9a75d11c63a6734b`: 15 suites,
  562 warnings, 2387 assertions, 0 failed.
- No migration, business write path, PILOT mutation, AI/provider change, or
  G3 implementation. Decision: `WALI_DASHBOARD_R1_IMPLEMENTED_PASS`; next:
  `RETURN_TO_CHATGPT_FOR_ACADEMIC_WALI_DASHBOARD_R1_AUDIT`.

## 2026-10-04 — Academic Web grade workflow G1 authorization/read surface

- Implemented a strict `academic.grades` feature boundary, exact subject-teacher/Wali/Waka read authorization, scoped sidebar navigation, and a read-only semester/class/subject grade worklist.
- Added focused tests for missing/disabled feature fail-closed behavior, role/resource scope, override non-bypass, GET-only routing, missing-vs-zero semantics, and no synthetic grade writes.
- Local PHPUnit was safely blocked by the guard because only protected `.env` pilot database `imtaq` was available; no pilot query or write was performed. Exact disposable PostgreSQL CI run `37194636793` on `f5fa452a34ed6f6a5c413721ca5894c7a9d23b55` passed: 16 tests, 2288 assertions, 0 failures (warnings remain non-blocking).
- No migration, schema/config change, grade business-data write, AI/provider mutation, or public Academic AI activation. G2 DRAFT-only state hardening remains the next task.

## 2026-10-04 — Academic Web grade workflow D1 decision ratification

- Verified the required grade services, models, authorization helper, correction model, feature registry, and focused tests against source.
- Ratified Waka process ownership; assigned subject-teacher input by exact TeachingAssignment scope; Wali checking; Waka approval/lock and correction review; atomic correction apply/reject semantics; `academic.grades` with no required permission; and `DRAFT → CHECKED → LOCKED`.
- Recorded `SemesterGradeEntryService` checked/locked update permissiveness as `IMPLEMENTATION_CRITICAL` for G2; no source patch was made.
- Updated the surface design, created D1 decision record, and reconciled routing metadata. No application source change, migration, seed, grade write, PILOT access, or AI/provider mutation.
- Decision: `GRADE_WORKFLOW_READY_FOR_IMPLEMENTATION`; next task `ACADEMIC-WEB-GRADE-WORKFLOW-G1-AUTHORIZATION-READ-SURFACE` after ChatGPT audit.

## 2026-10-04 — Academic Web grade workflow surface design

- Completed design-only reconnaissance of the existing semester grade backend and downstream report-card/transcript consumers.
- Created `codex/DESIGNS/ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN-2026-10-04.md`.
- Mapped canonical grain, DRAFT → CHECKED → LOCKED lifecycle, completeness semantics, Wali/Waka boundaries, audit actions, correction-request boundary, and downstream snapshot lineage.
- Confirmed no grade web routes/controllers/views exist; correction approval/apply authority, input ownership, and grade feature/permission mapping require owner decisions.
- No application source change, database write, migration, seed/import, pilot access, or AI/provider mutation. Decision: `GRADE_WORKFLOW_CONDITIONALLY_READY`; next atomic task returns to ChatGPT/project owner for design audit.

## 2026-10-04 — User & Akses controlled PILOT migration E1

- Owner-authorized E1 completed against PILOT `imtaq` after exact Git/CI, read-only target, pending-set, lock/writer, Super Admin, and verified-backup gates passed.
- Backup: custom-format, 1,051,065 bytes, mode 0600, `pg_restore --list` PASS; stored outside Git under the protected local PILOT backup directory.
- Guarded migration applied exactly the three approved User & Access migrations (`43→46`); only `UserAccessFeatureSeeder` ran.
- Independent read-only postflight: users 9/9 ACTIVE and must-change-password false; required permissions 3; grant matrix SUPER_ADMIN/WAKA/WALI = 3/1/0; features 13; orphan permissions 0; overrides/preferences 0/0; effective authorization matrix PASS; Public Academic AI OFF.
- No Academic attendance/student/schedule/roster/occurrence/teacher-participation write and no application source change. First-day Academic UAT remains HOLD / HUMAN_OBSERVATION_REQUIRED.
- Decision: `CONTROLLED_PILOT_MIGRATION_COMPLETED`; next task `RETURN_TO_CHATGPT_FOR_USER_ACCESS_CONTROLLED_PILOT_MIGRATION_E1_AUDIT`.

## 2026-10-02 — User & Akses permission bootstrap R1/R1R closeout

- R1 permission catalog/bootstrap implementation and R1R Academic dashboard fixture alignment are complete; production authorization code was not changed beyond the scoped seeder, no migration was added, and no PILOT database write occurred.
- Exact GitHub Actions runs `37011165609`, `37012002039`, and final metadata run `37012218885` passed; final HEAD `14ae0f4f47232220a12f60c18d180f5a2561051e`: PostgreSQL 18.6 disposable foundation, 15 passed, 536 warnings, 2267 assertions, 0 failed.
- Reconciled `PROJECT_STATE.json`, `EVIDENCE_INDEX.json`, `TEST_MATRIX.csv`, `NEXT_ACTION.md`, and `codex/CURRENT_TASK_CONTEXT.md`; preserved `SOC-MD-06`, `IMP-S12-007 = NOT_STARTED`, Public Academic AI OFF, and PILOT readiness as a separate owner-authorized gate.
- Next atomic task: `RETURN_TO_CHATGPT_FOR_PERMISSION_BOOTSTRAP_R1_AUDIT`.

## 2026-10-02 — User & Akses PILOT migration readiness review

- Review complete; readiness HOLD on `USER_ACCESS_PILOT_BOOTSTRAP_AUTHORITY_COMPATIBILITY`. No source, test, migration, config, or database changes.
- Clean entry and remote parity at 67672bdbf1dab18634c07517316780891debf467. CI 36976686299 is SUCCESS on executable 465be64a6d5263def7d1eb3554e35590426346ec; only two metadata files differ. No exact-head CI claim for 67672bd.
- Two independent PILOT imtaq / PostgreSQL 18.6 READ ONLY transactions; transaction_read_only=on; both ROLLBACK. Observed 43 applied / exactly 3 pending migrations, new schema absent, 9 users, 1 effective legacy SUPER_ADMIN, 0 duplicate/orphan Staff links.
- Actual permission catalog lacks academic.domain.manage/platform.institution.manage. Current feature seeder requires but does not create/grant them, so six Academic master groups and System Settings would lose existing authorized access. No repair attempted.
- Added complete bootstrap, account/password safety, rollout, rollback, and audit-boundary review: `codex/REVIEWS/SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-READINESS-2026-10-02.md`.
- Closeout checks PASS: project structure, tracked/untracked whitespace, JSON parsing, evidence/routing invariants, and protected executable-path no-drift check. No new database tests, commit, or push; six local governance/evidence files are the entire change set.
- Academic first-day UAT remains HOLD / HUMAN_OBSERVATION_REQUIRED; Public Academic AI OFF; SOC-MD-06 and IMP-S12-007 unchanged. Return to ChatGPT for readiness audit; no next task executed.

## 2026-09-24 — AI-PROVIDER-R1-D: admin observability, secret safety, and operational security

- Public Academic AI status now reads the authoritative `academic.ai.assistant_enabled` gate and remains separate from provider runtime; no activation control was added.
- Credential validation, provider exceptions, response errors, audit metadata, and browser behavior now exclude secrets and raw provider bodies. Provider-admin failures use normalized categories and correct credential/configuration/runtime entity semantics.
- Added dedicated admin provider throttle at 12 requests/minute per authenticated user/IP and in-flight submit disabling for sensitive forms; existing R1-B backend duplicate-DRAFT protection remains authoritative.
- Verification: R1-D provider 21/116, AI 56/242, Academic 387/1585, full 500/2053; PHP lint, Pint, and view cache PASS.
- Recovery: `recovery/ai-provider-r1d/AI-PROVIDER-R1D_20260924-220000/`; mandatory security remediation complete; next atomic task is `RETURN_TO_CHATGPT_FOR_R1D_AUDIT`.

## 2026-09-24 — AI-PROVIDER-R1-C: structural grounding and Academic data minimization

- Replaced model-text substring acceptance with structural tool-result grounding and explicit server-owned terminal states; identity resolution alone no longer authorizes attendance facts.
- Added deterministic handling for ambiguous/not-found identities, capability responses, unsupported writes, clarification, and tool failures while preserving server warnings.
- Excluded `student_attendance.reason_code` and free-text `notes` from AI attendance detail output; classified attendance reason as `FREE_TEXT_EXCLUDED` because storage is an unconstrained string. Source storage and business semantics remain unchanged.
- Verification: AI 52/210, Academic 383/1553, full 496/2021; PHP lint, Pint, and view cache PASS.
- Recovery: `recovery/ai-provider-r1c/AI-PROVIDER-R1C_20260924-210000/`; gate `GATE_C_AFTER_R1C=BLOCKED_PENDING_R1D`; next atomic task is `RETURN_TO_CHATGPT_FOR_R1C_AUDIT`.

## 2026-09-24 — AI-A5K-UI1R: Model ID discovery dropdown

- Replaced the confusing required Model ID text input with a credential-driven dropdown using the existing server-side discovery route.
- Added accessible loading, empty, and failure feedback; preserved the configuration payload and Super Admin authorization.
- No migration, database write, credential exposure, OpenAI live request, public activation, or Academic business-data mutation.
- Verification: provider tests 7/29, Blade lint, Pint, view cache, and browser rendering PASS.
- Recovery: `recovery/ai-a5k-ui1r/AI-A5K-UI1R_20260924-133500/`; next atomic task is return to ChatGPT for audit.

## 2026-09-24 — AI Provider verification HTTP 400 correction

- Fixed associative tool-schema serialization in configuration verification and runtime orchestration; Responses API now receives a JSON array in `tools`.
- Regression: AI suite 38/140 PASS; PHP lint, Pint, and view cache PASS.
- No schema migration, credential change, Academic business-data write, or public AI activation.

Append new entries; do not rewrite prior history without a documented correction.

## 2026-09-20 — AI-A5K-UI1 Super Admin provider navigation

- Audited the actual provider route/controller/view and confirmed the AI-A5K implementation already existed; only the active sidebar navigation entry was missing.
- Added the minimal `Pengaturan Sistem` link for Super Admin, preserved the existing AI-A5K backend, strengthened server-side Super Admin role enforcement, and clarified the zero-credential empty state.
- No migration, database write, credential seed, OpenAI request, public activation, or Academic business-data mutation.
- Verification: focused provider UI/RBAC 6/24, AI 37/133, Academic 368/1476, full 481/1944; lint, Pint, and view cache PASS.
- Closeout: `AI_A5K_UI1=CLOSED/ACCEPTED`; next atomic task is `RETURN_TO_CHATGPT_FOR_AI_A5K_UI1_AUDIT`.

## 2026-09-20 — AI-A4: Waka Academic Assistant Chat UI
- Added the feature-gated, read-only assistant panel to the Waka dashboard using the existing AI-A3 same-origin POST endpoint. Browser sends only `question` with CSRF; answer/warnings render as text, with loading, duplicate-submit prevention, sanitized errors, responsive layout, and accessibility feedback.
- PHPUnit test harness now forces SQLite in-memory/testing/array cache-session even when stale application config is cached; pilot PostgreSQL remains untouched.
- Verification: targeted dashboard/AI/auth 72/328, Academic 358/1434, full 471/1902; view cache, PHP lint, and Pint PASS.
- Recovery: `recovery/ai-a4/AI-A4_20260920-095425/`; next atomic task is return to ChatGPT for AI-A4 audit.

## 2026-09-15 — JOINT-2A: truthful joint attendance identity and roster breakdown
- Attendance page kini memakai read model effective scope: sesi gabungan menampilkan “Kelas gabungan”, daftar kelas, breakdown jumlah peserta, dan badge kelas per santri.
- Attribution memakai snapshot participant plus enrollment yang efektif pada tanggal sesi; ambiguity/unmapped tidak dipaksa ke anchor. Roster tetap satu form dan KPI/finalization tetap session-level.
- Verifikasi: StudentAttendanceUiTest 32/249 dan Academic regression 274/1143 lulus; view cache, PHP lint, dan Pint lulus.

## 2026-09-15 — JOINT-1C: scope-aware joint class conflict integrity
- Menambahkan AcademicClassScopeResolver untuk effective scope ScheduleRule/ClassSession dengan fallback legacy anchor class.
- Conflict checker, reschedule, extra session, dan revision occupancy guard kini memeriksa irisan scope class; teacher-only swap, importer mapping, joint grain, dan temporal semantics tetap.
- Verifikasi: targeted conflict/joint/revision/session/reschedule/extra 20/58 dan Academic regression 273/1137 lulus; PHP lint dan Pint lulus.
- Database exclusion constraint secondary scope serta real PostgreSQL concurrency verification tetap menjadi hardening gap tertunda.

## 2026-09-15 — JOINT-1B: preserve joint scope metadata on schedule-rule revision
- ScheduleRuleRevisionService kini membaca group dari source rule yang di-lock dan menyalin `class_id` serta `scope_role` ke satu revised rule sebelum generator membuat occurrence baru.
- Source groups tetap utuh, legacy rule tanpa group tetap memakai fallback anchor assignment, dan generated session mewarisi scope gabungan.
- Verifikasi: targeted revision/joint/session/reschedule 12/42 dan Academic regression 269/1131 lulus; PHP lint dan Pint lulus.
- Secondary joint-scope conflict checking tetap ditunda ke JOINT-1C.

## 2026-09-15 — JOINT-1A: preserve joint scope metadata on reschedule
- RescheduleService kini membaca `scopeGroups` dari source yang sudah di-lock dan menyalin hanya `class_id` serta `scope_role` ke satu replacement session.
- Source groups tetap utuh; source tanpa groups tetap memakai fallback `ClassSession.class_id`; participant dan attendance facts tidak disalin.
- Verifikasi: RescheduleServiceTest 5/15, joint/session regression 12/40, Academic regression 268/1126 lulus; view cache, PHP lint, dan Pint lulus.
- Conflict checking untuk secondary joint class tetap ditunda ke JOINT-1C.

## 2026-09-14 — DASH-UI-7: final responsive, accessibility, dan micro-polish dashboard
- Menyembunyikan persentase duplikat pada heading Status Operasional, menambahkan focus-visible yang konsisten, dan memastikan control rentang tren nyaman disentuh.
- KPI tablet memakai grid dua kolom; DASH-UI-1 sampai DASH-UI-6, role variant Wali/Super Admin, null/zero semantics, serta full-width canvas tetap dipertahankan.
- Verifikasi: dashboard test 37 test/171 assertion dan Academic regression 266 test/1120 assertion lulus; view cache, PHP lint, dan Pint lulus.

## 2026-09-14 — DASH-UI-6: reflow canvas dashboard dan rekonsiliasi density
- Status Operasional dan Aksi Cepat tetap menjadi baris operasional atas; Pemantauan Kelas, Pengisian Kehadiran Wali, dan Tren Kehadiran mengalir full-width tanpa rail kanan kosong.
- Menormalisasi gap/layout card secara presentation-only dan mempertahankan seluruh konten, nilai, formula, filter, role variant, serta semantics DASH-UI-1 sampai DASH-UI-5.
- Verifikasi: dashboard test 37 test/170 assertion dan Academic regression 266 test/1119 assertion lulus; view cache, PHP lint, dan Pint lulus.

## 2026-09-14 — DASH-UI-5: modernisasi visualisasi tren kehadiran
- Memisahkan metrik `Kehadiran` dan `Kelengkapan` menjadi dua progress track dengan warna dan label yang berbeda, serta menampilkan dukungan `resolved/eligible` dan jumlah yang belum tervalidasi.
- Mempertahankan sumber data, formula, denominator, rentang 7/14/30 hari, route, query, null/zero semantics, dan ARIA progressbar; tidak ada perubahan backend atau business logic.
- Verifikasi: dashboard test 37 test/168 assertion dan Academic regression 266 test/1117 assertion lulus; view cache, PHP lint, dan Pint lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-EXPORT-PERIOD: selaraskan filter dashboard dan ekspor
- Filter `month` kini diprioritaskan secara konsisten pada halaman Dashboard dan ekspor CSV; parameter `from`/`to` yang tertinggal tidak lagi membuat ekspor mengambil periode berbeda dari layar.
- Tautan ekspor juga dinormalisasi agar hanya membawa filter periode yang aktif, sehingga URL tidak lagi menampilkan kombinasi parameter yang membingungkan.
- Validasi rentang tanggal dan batas tahun ajaran aktif dipusatkan pada resolver periode bersama tanpa mengubah data historis.
- Verifikasi: AcademicRoleDashboardServiceTest 20 test/67 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-SNAPSHOT-BOUNDARY: batasi snapshot Juli pada periode Juli penuh
- Sumber snapshot historis tidak lagi dipakai untuk rentang yang melebar sampai 1 Agustus; periode campuran kembali memakai transaksi live agar angka tidak tercampur.
- Verifikasi: AcademicRoleDashboardServiceTest 21 test/70 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-LIVE-ATTENDANCE: gunakan transaksi live untuk Juli
- Dashboard Waka kini selalu menghitung ringkasan kelas, status periode, dan tren dari transaksi kehadiran live, termasuk ketika memilih Juli 2026.
- Snapshot historis tetap tersedia untuk kebutuhan laporan historis, tetapi tidak lagi menjadi sumber dashboard operasional.
- Verifikasi: AcademicRoleDashboardServiceTest 21 test/70 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-TIMEZONE: tetapkan timezone operasional Indonesia
- Default timezone aplikasi ditetapkan ke `Asia/Jakarta` dengan override `APP_TIMEZONE`, agar interpretasi tanggal, hari, dan status masa depan sesi konsisten dengan operasional sekolah.
- Verifikasi: AcademicRoleDashboardServiceTest 22 test/71 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-DUE-DENOMINATOR: selaraskan indikator sesi jatuh tempo
- Metrik kelengkapan kelas dan tren live kini hanya memasukkan sesi yang sudah selesai waktunya; sesi mendatang/berlangsung tidak menurunkan persentase kelengkapan.
- Sesi yang belum selesai tetap dihitung oleh indikator status sebagai berlangsung atau akan datang.
- Verifikasi: AcademicRoleDashboardServiceTest dan AttendanceSemanticMetricsServiceTest 24 test/81 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-JOINT-SESSIONS: selaraskan sesi kelas gabungan
- Status periode, tren, dan daftar sesi Wali kini mencari sesi melalui `class_session_groups` selain `class_id`, sehingga sesi gabungan tidak hilang dari indikator live.
- Query mengambil setiap sesi satu kali; pembagian peserta per kelas tetap menjadi tanggung jawab metrik kelas canonical.
- Verifikasi: AcademicRoleDashboardServiceTest dan JointClassMetricsTest 25 test/78 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-TREND-DATES: tampilkan slot hari kosong pada tren
- Grafik tren kini mengembalikan seluruh tanggal dalam rentang terpilih; hari tanpa sesi tetap terlihat sebagai `Belum tersedia`, bukan hilang dari grafik.
- Sesi mendatang tetap tidak dihitung sebagai transaksi live, sehingga slot tanggalnya tidak membuat kelengkapan palsu.
- Verifikasi: AcademicRoleDashboardServiceTest dan JointClassMetricsTest 25 test/78 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-STATUS-LABELS: bedakan status indikator live
- KPI membedakan `Belum ada sesi selesai` dari data yang memang belum tersedia; status periode membedakan `Belum ada sesi jatuh tempo` dari sesi yang belum disahkan.
- Ringkasan kelas menggunakan istilah `sesi selesai` agar tidak disalahartikan sebagai jumlah santri.
- Verifikasi: AcademicRoleDashboardServiceTest dan JointClassMetricsTest 25 test/79 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-MONTH-FILTER: pertegas prioritas filter bulan
- Pemilihan bulan diberi petunjuk aksesibilitas bahwa tanggal manual diabaikan ketika bulan dipilih.
- Regression test memastikan pilihan Agustus tetap menghasilkan 1–31 Agustus walaupun URL membawa tanggal Juli lama.
- Verifikasi: AcademicRoleDashboardServiceTest 25 test/80 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-TEACHER-ATTENDANCE: tampilkan kehadiran guru live
- Dashboard kini membaca `SessionTeacherParticipation` untuk sesi selesai dan menampilkan persentase guru hadir, jumlah tercatat, dan jumlah tidak hadir.
- Label tetap membedakan belum ada sesi selesai dengan sesi yang sudah ada tetapi belum diisi.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/89 assertion serta `php artisan view:cache` lulus.

## 2026-09-11 — FIX-ACADEMIC-DASHBOARD-TEACHER-RESPONSIVE: rapikan indikator guru di layar kecil
- Nilai dan catatan indikator guru kini membungkus aman pada tablet/ponsel; heading kartu juga berubah menjadi susunan vertikal agar tidak berhimpitan.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/89 assertion serta `php artisan view:cache` lulus.

## 2026-09-07 — IMP-REL-002 atomic checkpoint: staging release gate preparation
- Full regression ulang lulus: 216 test dengan 745 assertion.
- Menyusun checklist release staging untuk release identity, environment, backup/restore, monitoring, smoke test, dan rollback.
- Menemukan blocker operasional: checkout bukan Git worktree, staging host/database belum dikonfigurasi, dan backup/restore/monitoring/rollback rehearsal belum memiliki bukti.
- Tidak ada deployment, backup claim, perubahan secret, atau perubahan data dilakukan. Status aman: local UAT only.

## 2026-09-07 — IMP-REL-001 atomic checkpoint: full regression dan release review
- Full PHPUnit lulus: 216 test dengan 745 assertion.
- Route inventory menunjukkan 39 route Academic/Admin; migration snapshot berstatus RUN.
- Data integrity lokal: 84 snapshot, 84 lineage, 20 attendance sesi pilot, dan 5 ringkasan kelas Juli.
- Local readiness PASS; staging/production deployment belum dilakukan. Backup/restore, secrets, monitoring, dan rollback tetap menjadi gate release.

## 2026-09-06 — IMP-UAT-004 atomic checkpoint: smoke test visual lintas role
- Super Admin dashboard terbuka dan layout spacing terlihat konsisten.
- Waka Akademik berhasil membuka dashboard role-specific dengan dua kelas; akses `/admin/academic` ditolak sesuai RBAC.
- Wali Kelas berhasil membuka laporan Juli dan detail Kelas 3A; 15 snapshot santri tampil dengan layout mobile yang rapi.
- Tidak ada perubahan kode atau data pada checkpoint ini.

## 2026-09-06 — IMP-UI-033 atomic checkpoint: spacing lintas halaman
- Menambah jarak vertikal antar-card, jarak grid, dan ruang header pada shared Admin styles; mobile diberi nilai yang lebih ringkas namun tetap tidak menempel.
- Perubahan hanya presentasi dan berlaku konsisten pada halaman Academic/Admin yang memakai partial tersebut.
- Verifikasi: 154 test/493 assertion PASS; Blade cache dan PHP lint PASS.

## 2026-09-06 — IMP-UI-032 atomic checkpoint: detail report responsive polish
- Redesigned detail laporan per-santri dengan summary cards, heading hierarchy, table styling, RTL name handling, and mobile card layout.
- Preserved read-only behavior, data source, RBAC, and attendance semantics.
- Verification: targeted suite 28 test/89 assertion PASS; Blade cache and PHP lint PASS.

## 2026-09-06 — IMP-REPORT-DETAIL-001 atomic checkpoint: detail memakai snapshot permanen
- Mengalihkan query detail laporan bulanan dari staging ke `monthly_student_attendance_snapshots` dengan eager-load canonical Student dan scope kelas yang sama.
- Empty-state tetap aman jika tabel snapshot tidak tersedia; tidak ada mutation attendance.
- Verifikasi: targeted suite 28 test/89 assertion PASS; Blade cache dan PHP lint PASS; class totals 20/19/10/15/20, total 84.

## 2026-09-06 — IMP-MIG-015 atomic checkpoint: snapshot import and reconciliation
- Added transaksional `MonthlyStudentAttendanceSnapshotImportService` with import batch/file/row lineage, exact mapping gate, batch lifecycle, and rerun idempotency.
- Imported 84 per-student monthly snapshots locally; batch `IMTAQ-202607-STUDENT-SNAPSHOT` reached `IMPORTED` and 84 lineage rows were created.
- Post-import reconciliation matched July totals: present 1126, permission 39, sick 29, absent 4, eligible 1198, non-eligible 142; duplicate source keys 0.
- Confirmed existing `student_attendance` remains 20 pilot rows and class summaries remain 5 rows; no session attendance fact was created.

## 2026-09-06 — IMP-MIG-015 atomic checkpoint: snapshot migration and mapping dry-run
- Applied the additive `monthly_student_attendance_snapshots` migration locally; the new table remains empty.
- Added read-only `MonthlyStudentAttendanceSnapshotMappingService` for repeatable exact mapping from staging to canonical Student/enrollment.
- Database dry-run mapped 84/84 rows with 0 errors and 0 snapshot rows created.
- Verification: targeted validator tests 3/3 with 7 assertions; PHP lint PASS. Snapshot import and UI consumption remain separate checkpoints.

## 2026-09-06 — IMP-AUDIT-002 atomic checkpoint: fallback detail staging
- Menambahkan pemeriksaan ketersediaan tabel staging sebelum halaman detail per-santri melakukan query.
- Jika staging tidak ada, sistem menampilkan pesan informatif dan tetap menyediakan rekap kelas; tidak ada 500 dan tidak ada pembuatan fakta attendance production.
- Verifikasi: full suite 213 test dengan 738 assertion PASS; Blade cache PASS; PHP lint PASS; route inspection PASS.
- Resume point: `IMP-MIG-015` — putuskan penyimpanan detail historis permanen atau tutup audit.

## 2026-09-06 — IMP-AUDIT-001 atomic checkpoint: koreksi dashboard lintas role
- Menyembunyikan tautan “Perlu Perhatian Kehadiran” dari Wali Kelas karena backend memang membatasi halaman itu untuk Admin/Waka.
- Mengganti kode role teknis menjadi Super Admin, Waka Akademik, dan Wali Kelas pada dashboard.
- Verifikasi: targeted 13 test/32 assertion PASS; full suite 213 test/738 assertion PASS; Blade cache PASS.
- Resume point: `IMP-MIG-015` — tindak lanjut temuan staging/detail atau tutup audit dashboard.

## 2026-09-05 — IMP-UI-031 atomic checkpoint: istilah laporan disahkan
- Mengganti istilah UI “Sudah diterbitkan” menjadi “Sudah disahkan” dan “Setujui & terbitkan” menjadi “Sahkan laporan”.
- Status internal `PUBLISHED`, audit, dan lifecycle laporan tidak berubah.
- Verifikasi: Blade cache PASS; 6 test Admin/dashboard dengan 20 assertion PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — review bahasa UI lain atau tutup checkpoint.

## 2026-09-05 — IMP-UI-030 atomic checkpoint: label Detail mobile
- Memperbaiki label “Detail” yang terpecah menjadi dua baris pada kartu laporan mobile.
- Tombol “Lihat santri” sekarang tampil penuh tanpa label ganda.
- Verifikasi: Blade cache PASS; 6 test Admin/dashboard dengan 20 assertion PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — refresh browser untuk memastikan hasil atau tutup checkpoint.

## 2026-09-05 — IMP-UI-029 atomic checkpoint: koreksi kartu laporan mobile
- Mengatasi lebar minimum tabel global yang mendorong nilai keluar layar pada tampilan mobile.
- Kartu per kelas sekarang menampilkan label dan angka dalam satu area yang terkontrol.
- Tidak mengubah nilai laporan, sumber staging, atau aturan bisnis.
- Verifikasi: Blade cache PASS; 6 test Admin/dashboard dengan 20 assertion PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — refresh browser untuk review atau tutup checkpoint.

## 2026-09-05 — IMP-UI-028 atomic checkpoint: perapian rekap bulanan responsif
- Memperbaiki tabel rekap kelas yang terpotong/terlalu padat pada layar sempit.
- Desktop tetap tabel ringkas; mobile berubah menjadi kartu per kelas dengan label nilai dan tombol “Lihat santri” yang jelas.
- Tidak mengubah nilai laporan, sumber detail staging, atau business rule.
- Verifikasi: Blade cache PASS; 6 test Admin/dashboard dengan 20 assertion PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — review visual ulang atau tutup checkpoint.

## 2026-09-05 — IMP-ADM-013 atomic checkpoint: detail kehadiran per kelas dan santri
- Menambahkan tautan “Lihat santri” dari rekap bulanan ke halaman detail per kelas.
- Detail menampilkan total kelas serta rekap hadir/izin/sakit/absen setiap santri dan tingkat keaktifannya.
- Detail membaca snapshot staging Juli secara read-only karena production baru menyimpan ringkasan kelas; tidak ada fakta attendance baru yang dibuat.
- Wali Kelas tetap dibatasi ke kelas tanggung jawabnya.
- Verifikasi: 4 test Student/Admin dengan 15 assertion PASS; Blade cache PASS; route inspection PASS; PHP lint PASS; sanity staging PASS.
- Resume point: `IMP-MIG-015` — review UI detail atau putuskan apakah perlu migrasi snapshot per-santri secara kontraktual.

## 2026-09-05 — IMP-ADM-012 atomic checkpoint: ubah kelas santri
- Menambahkan pilihan Kelas aktif dan tanggal Berlaku mulai pada Edit Santri.
- Perubahan kelas memakai enrollment interval baru; enrollment lama ditutup, sehingga histori tetap tersedia.
- Tidak mengubah UUID internal atau fakta attendance.
- Verifikasi: 9 test terarah dengan 29 assertion PASS; Blade cache PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — review UI terautentikasi atau lanjut ke pengelolaan enrollment.

## 2026-09-05 — IMP-ADM-011 atomic checkpoint: tambah dan edit santri
- Menambahkan tombol/form tambah santri dan halaman edit santri dari dashboard Admin.
- Tambah santri membuat UUID internal otomatis dan enrollment kelas aktif; nama Arab, tahun masuk, NIS, dan NISN dapat diisi sesuai ketersediaan.
- Edit santri tidak mengubah UUID maupun riwayat enrollment/attendance.
- Verifikasi: 6 test Admin dengan 20 assertion PASS; Blade cache PASS; route inspection PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — review UI form tambah/edit atau lanjut ke kebutuhan master kelas.

## 2026-09-05 — IMP-UI-027 atomic checkpoint: jarak form Edit Kelas
- Menambahkan jarak visual yang konsisten antara pilihan Status dan tombol Simpan perubahan.
- Tidak mengubah alur edit, validasi, data kelas, atau business rule.
- Verifikasi: 3 test Admin kelas dengan 11 assertion PASS; Blade cache PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — review visual lanjutan atau tutup checkpoint.

## 2026-09-05 — IMP-UI-026 atomic checkpoint: perapian roster Master Santri
- Memperbaiki kepadatan tabel roster: lebar kolom ditata, teks Arab diratakan kanan, input NIS/NISN diperjelas, dan tombol tidak berhimpitan.
- Filter pencarian/kelas dibuat grid responsif; pada layar kecil tabel tetap dapat digeser secara terkontrol.
- Label teknis “Pilot” dibersihkan pada tampilan kelas tanpa mengubah data sumber.
- Verifikasi: 5 test Admin dengan 16 assertion PASS; Blade cache PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — review visual lanjutan atau tutup rangkaian roster.

## 2026-09-05 — IMP-ADM-010 atomic checkpoint: pencarian dan filter kelas roster
- Menambahkan pencarian nama Indonesia/Arab dan filter kelas aktif pada roster 84 santri.
- Pagination mempertahankan parameter filter; tombol Reset mengembalikan daftar penuh.
- Tidak ada perubahan database, enrollment, UUID, NIS/NISN, atau fakta attendance.
- Verifikasi: 5 test Admin dengan 16 assertion PASS; Blade cache PASS; route inspection PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — review UI terautentikasi atau tutup rangkaian master roster.

## 2026-09-05 — IMP-ADM-009 atomic checkpoint: pengisian NIS/NISN
- Menambahkan form per santri pada roster Admin untuk mengisi NIS dan NISN ketika data resmi sudah tersedia.
- Validasi backend: opsional, panjang dibatasi, dan tidak boleh duplikat.
- UUID internal tetap tidak berubah; tidak ada pembuatan ID resmi otomatis dan tidak ada perubahan attendance historis.
- Verifikasi: 5 test Admin dengan 14 assertion PASS; Blade cache PASS; route inspection PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — tambahkan pencarian/filter roster atau lakukan review UI terautentikasi.

## 2026-09-05 — IMP-ADM-008 atomic checkpoint: daftar 84 santri dan kelas aktif
- Menambahkan halaman read-only Admin Akademik `/admin/academic/students` untuk roster Juli 2026.
- Daftar difilter ke 84 enrollment aktif dari import master; data contoh lama tidak dihapus dan tidak tercampur.
- Menampilkan nama Indonesia, nama Arab, kelas aktif, tahun masuk internal, serta status NIS/NISN yang belum diisi.
- Akses dibatasi backend untuk `SUPER_ADMIN` dan `ADMIN_AKADEMIK`; non-admin menerima 403.
- Verifikasi: 4 test Admin dengan 9 assertion PASS; Blade cache PASS; route inspection PASS; PHP lint PASS.
- Resume point: `IMP-MIG-015` — siapkan pengisian NIS/NISN atau tambahkan pencarian/filter roster.

## 2026-09-05 — IMP-ADMIN-ROOT-002 atomic checkpoint: akses seluruh dashboard akademik
- Super Admin diberi akses read-only ke `/academic/dashboard` dengan scope seluruh kelas, sesuai hak akses penuh yang disepakati.
- Regresi lulus: 12 test dengan 27 assertion; browser memverifikasi menu Admin dan Dashboard Akademik.
- Tidak ada perubahan data attendance atau aturan bisnis.
- Resume point: `IMP-MIG-011` — pilih kebutuhan lanjutan atau tutup rangkaian import Juli.

## 2026-09-05 — IMP-ADMIN-ROOT-001 atomic checkpoint: dashboard Super Admin
- Menambahkan `SuperAdminLocalSeeder` untuk akun `superadmin.pilot@example.test` dengan role `SUPER_ADMIN`.
- Login browser berhasil dan dashboard menampilkan `Masuk sebagai: Super Admin`.
- Existing user, mapping, dan data attendance tidak diubah.
- Resume point: `IMP-MIG-011` — pilih kebutuhan lanjutan atau tutup rangkaian import Juli.

## 2026-09-01 — Workspace packaging
- Consolidated P1–P12 and Consistency Patch R1 into a self-contained Codex project folder.
- Added persistent `AGENTS.md`, `PROJECT_STATUS.md`, `NEXT_ACTION.md`, task queue and P13 implementation roadmap.
- No application feature code created.
- Next executable task when user explicitly starts implementation: `IMP-S0-001`.

## 2026-09-02 — IMP-S0-001 checkpoint: environment inspection
- Atomic step: inspected the target path and local foundation toolchain before scaffold initialization.
- Verified `application/web/` is absent; no existing application was overwritten.
- Targeted check: `python3 scripts/check_project_structure.py` — PASS (`domains=8`, current task=`IMP-S0-001`).
- Blocker: `php`, `composer`, `laravel`, and `psql` are unavailable on PATH; Docker client exists but Docker daemon is not running. No package installation, build, migration, or database operation was started.
- Git evidence: current workspace is not detected as a Git worktree from the task root; no branch/status claim made.
- Resume point: provide a usable PHP/Composer/Laravel toolchain (or start the Docker daemon), then re-run the environment check and initialize the Laravel scaffold under `application/web/`.

## 2026-09-02 — IMP-S0-001 checkpoint: quick re-verification
- Selected horizon: QUICK; no scaffold, package installation, build, migration, or database operation performed.
- Re-verified `application/web/` remains absent; PHP, Composer, Laravel, and `psql` remain unavailable; Docker daemon remains unreachable.
- Targeted check: `python3 scripts/check_project_structure.py` — PASS.
- Resume point unchanged: make PHP/Composer/Laravel available or start Docker, then initialize and verify the Laravel scaffold.

## 2026-09-02 — IMP-S0-001 checkpoint: foundation scaffold
- Selected horizon: EXTENDED; initialized Laravel application under `application/web/` using Laravel skeleton `v13.10.1` and resolved Framework `v13.30.1`.
- Installed local toolchain through Homebrew: PHP `8.5.10`, Composer `2.10.3`.
- Added PostgreSQL-ready placeholder configuration in `.env.example` and local `.env`; no real secrets committed. Added `FOUNDATION.md` and Change Manifest.
- Laravel post-create hooks ran only the generated default SQLite migrations locally; no business/domain migration was added.
- Verification: `composer validate --strict` — PASS; `php artisan test` — PASS (2 tests, 2 assertions); `php artisan --version` — PASS; `python3 scripts/check_project_structure.py` — PASS.
- Live PostgreSQL connection was not tested because no PostgreSQL server/client is available locally.
- Repository progress script ran successfully; task queue evidence remains `IMP-S0-001` at 0%/READY, so no task-status claim was advanced at this checkpoint.
- Resume point: decide whether to perform PostgreSQL connectivity setup/verification and then complete task closeout/status updates, or stop with the foundation as saved WIP.

## 2026-09-02 — IMP-S0-001 closeout
- Selected horizon: STANDARD follow-through from the EXTENDED checkpoint; installed PostgreSQL `18.6`, started its local Homebrew service, and created development database `imtaq` with role `imtaq_app`.
- Live Laravel connection verification: `php artisan db:show --database=pgsql` — PASS; PostgreSQL `18.6`, database `imtaq`, user `imtaq_app`, 0 tables.
- Final verification: `php artisan test` — PASS (2 tests, 2 assertions); `composer validate --strict` — PASS; project structure check — PASS.
- Updated task queue: `IMP-S0-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S0-002`, which remains `NOT_STARTED`.
- No domain schema, business migration, external provider, staging, or production operation was performed.
- Task closeout complete; safe resume point is the start of `IMP-S0-002` after its task context is activated.

## 2026-09-02 — IMP-S0-002 closeout
- Atomic step: documented application/module ownership and safe-change conventions in `application/web/docs/DEVELOPMENT_CONVENTIONS.md` and created the task context for repeatable routing.
- Coverage: modular ownership, cross-module boundaries, change classes, impact statement, protected-zone escalation, Git/change IDs, Change Manifest, migration immutability, secret separation, test/release/rollback gates, and stop conditions.
- Verification: `composer validate --strict` — PASS; `php artisan test` — PASS (2 tests, 2 assertions); `python3 scripts/check_project_structure.py` — PASS.
- Updated task queue: `IMP-S0-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S0-003`, which remains `NOT_STARTED`.
- No runtime source, business schema, migration, RBAC, provider, staging, or production operation was performed.
- Resume point: activate `IMP-S0-003` and implement only its baseline CI/local verification and deployment-readiness workflow.
- Final correction: updated `NEXT_ACTION.md` to reference the not-yet-activated `IMP-S0-003` context; reran app regression from `application/web/` — `php artisan test` PASS (2 tests, 2 assertions) and `composer validate --strict` PASS.

## 2026-09-02 — IMP-S0-003 closeout
- Atomic step: added repeatable local verification at `application/web/scripts/verify-foundation.sh`, CI workflow at `.github/workflows/application-foundation.yml`, and deployment-readiness/rollback documentation at `application/web/docs/DEPLOYMENT_READINESS.md`.
- Verification: foundation script — PASS; `php artisan test` — PASS (2 tests, 2 assertions); `composer validate --strict` — PASS; `python3 scripts/check_project_structure.py` — PASS; shell syntax — PASS.
- No migration, backup/restore, staging, production deployment, secret provisioning, or business feature implementation was performed.
- Updated task queue: `IMP-S0-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-001`, which remains `NOT_STARTED` pending its task context.
- Resume point: activate `IMP-S1-001`; load Shared Core-specific authority before any identity/schema/RBAC work.

## 2026-09-02 — IMP-S1-001 closeout
- Atomic step: added Shared Core migration, UUID models and feature tests for `organizational_units`, `locations`, `staff`, and `staff_organizational_assignments`.
- PostgreSQL integration initially exposed an inline self-referential FK ordering defect; the minimal fix adds the `parent_unit_id` FK after table creation. The corrected migration ran successfully on the empty local `imtaq` database.
- Verification: targeted Core tests 2/2 with 17 assertions; full suite 4/4 with 19 assertions; Composer validation PASS; migration status PASS; project structure PASS.
- Local PostgreSQL now reports 13 tables including the four Shared Core tables; no staging/production database was touched.
- Updated task queue: `IMP-S1-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-002`, which remains `NOT_STARTED` pending its task context.
- Known deferred integrity work: hierarchy-cycle prevention and overlapping primary assignment validation remain service-level follow-up; RBAC/account links were intentionally not implemented.
- Resume point: activate `IMP-S1-002` and load the Shared Core student contracts before implementing the canonical Student master.

## 2026-09-02 — IMP-S1-002 closeout
- Atomic step: added canonical `students` migration and Shared Core `Student` model with UUID identity, permanent unique `student_code`, canonical identity fields, versioning and timestamps.
- Explicitly excluded current class/status/halaqah/room fields, identifier history, lifecycle history, cohort relation and enrollment from this task.
- Verification: targeted Student tests 2/2 with 20 assertions; full suite 6/6 with 39 assertions; Composer validation PASS; PostgreSQL migration batch 3 PASS; migration status and database inspection PASS.
- Updated task queue: `IMP-S1-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-003`, which remains `NOT_STARTED` pending its task context.
- Resume point: activate `IMP-S1-003` and load the identifier/version/audit contracts before adding `student_identifiers`.

## 2026-09-02 — IMP-S1-003 closeout
- Atomic step: added `student_identifiers` migration and `StudentIdentifier` model with NIS/NISN types, verification/record status, validity interval, optional scope and version fields.
- Added PostgreSQL/SQLite-compatible partial uniqueness: one active identifier per Student/type/scope and globally unique active NISN value; superseded history remains retainable.
- Verification: targeted identifier tests 3/3 with 19 assertions; full suite 9/9 with 58 assertions; Composer validation PASS; PostgreSQL migration batch 4 PASS; required partial indexes confirmed.
- No append-only audit log, correction-request workflow, lifecycle history, enrollment, RBAC, staging or production operation was added.
- Updated task queue: `IMP-S1-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-004`, which remains `NOT_STARTED` pending its task context.
- Resume point: activate `IMP-S1-004` and load lifecycle/status-history contracts before adding effective-dated Student status intervals.

## 2026-09-02 — IMP-S1-004 closeout
- Atomic step: added effective-dated `student_status_history` migration and model with candidate lifecycle statuses, decision/reason/actor context, versioning and `[effective_from, effective_until)` intervals.
- Added PostgreSQL `btree_gist` exclusion constraint to block overlapping intervals per Student, plus model validation for invalid/overlapping intervals in local SQLite tests.
- Verification: targeted status-history tests 3/3 with 16 assertions; full suite 12/12 with 74 assertions; Composer validation PASS; PostgreSQL migration batch 5 PASS; exclusion constraint confirmed.
- No current-status resolver, eligibility service, append-only audit, correction workflow, enrollment, RBAC, staging or production operation was added.
- Updated task queue: `IMP-S1-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-005`, which remains `NOT_STARTED` pending its task context.
- Resume point: activate `IMP-S1-005` and load Shared Platform RBAC authority before adding roles, permissions or scope assignments.

## 2026-09-02 — IMP-S1-005 closeout
- Atomic step: added Shared Platform RBAC migration and models for `roles`, `permissions`, `role_permissions`, and effective/scoped `user_role_assignments`; added User relationships.
- Preserved the boundary that Users are accounts, Staff is separate, and no `users.role` string or default institutional role policy was added.
- Verification: targeted RBAC tests 2/2 with 12 assertions; full suite 14/14 with 86 assertions; Composer validation PASS; PostgreSQL migration batch 6 PASS; database reports 20 tables.
- Fixed effective-date query comparison with `whereDate()` for consistent SQLite/PostgreSQL behavior.
- No login/SSO, domain resolver, default role seed, account link, append-only audit, staging or production operation was added.
- Updated task queue: `IMP-S1-005` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-006`, which remains `NOT_STARTED` pending its task context.
- Resume point: activate `IMP-S1-006` and load audit/correction contracts before implementing append-only audit infrastructure.

## 2026-09-02 — IMP-S1-006 closeout
- Atomic step: added `audit_logs` and `correction_requests` migration, append-only `AuditLog` model/service, pending `CorrectionRequest` model/service submission, and governance tests.
- PostgreSQL trigger `audit_logs_append_only` blocks UPDATE/DELETE; local model tests also reject audit mutation. Correction service records intent as `PENDING` and never applies business changes.
- Verification: targeted audit/correction tests 3/3 with 22 assertions; full suite 17/17 with 108 assertions; Composer validation PASS; PostgreSQL migration batch 7 PASS; trigger confirmed.
- No automatic approval/application, domain correction policy, business mutation, staging or production operation was added.
- Updated task queue: `IMP-S1-006` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-007`, which remains `NOT_STARTED` pending its task context.
- Resume point: activate `IMP-S1-007` and load Guardian/relationship/contact contracts before adding those Shared Core records.

## 2026-09-03 — IMP-S1-007 closeout
- Atomic step: added canonical Guardian master, Student-specific guardian relationships, and baseline Guardian contact channels with effective dates, status/version fields, scoped authorization, and PostgreSQL partial uniqueness constraints.
- Preserved boundaries: Guardian is not a User account; contact channels contain no communication consent; no automatic merge, account link, recipient policy, provider integration, or Parent Portal behavior was added.
- Verification: `composer validate --strict` PASS; targeted Guardian tests 3/3 with 11 assertions; full suite 20/20 with 119 assertions; PostgreSQL migration batch 8 PASS; `migrate:status` and `db:show` PASS with 25 tables.
- Updated task queue: `IMP-S1-007` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S1-008`, which remains `NOT_STARTED` pending activation.
- No staging/production database or external provider was touched. Resume point: activate `IMP-S1-008` and load the Shared Core contract/DQ/security test scope.

## 2026-09-03 — IMP-S1-008 closeout
- Atomic step: added focused Shared Core contract tests for orphan-reference prevention and expired RBAC assignment behavior, and added recursive redaction of credential-like keys in audit payloads before persistence.
- Preserved boundaries: no schema/migration change, Guardian account linking, consent, provider, Parent Portal, SSO, AI, or domain feature was added.
- Verification: targeted contract tests 3/3 with 6 assertions; full suite 23/23 with 125 assertions; `composer validate --strict` PASS; project structure and foundation verification PASS.
- Updated task queue: `IMP-S1-008` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S2-001`, which remains `NOT_STARTED` pending activation.
- No PostgreSQL migration was needed for this test/service-only step; no staging/production operation was performed. Resume point: activate `IMP-S2-001` and load Academic class/enrollment authority.

## 2026-09-03 — IMP-S2-001 closeout
- Atomic step: added shared `academic_years` reference plus Academic `grade_levels`, year-specific `classes`, and canonical `subjects` masters with UUIDs, status/version fields, FK ownership, configurable section codes, and class uniqueness by year/unit/grade/section.
- Preserved boundaries: Student remains independent from class placement; no enrollment, homeroom, scheduling, attendance, semester, or score fields were added.
- Verification: targeted Academic master tests 4/4 with 16 assertions; full suite 27/27 with 141 assertions; Composer validation PASS; PostgreSQL migration batch 9 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 29 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S2-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S2-002`, which remains `NOT_STARTED` pending activation. Resume point: load enrollment authority and implement effective-dated enrollment with overlap protection.

## 2026-09-03 — IMP-S2-002 closeout
- Atomic step: added effective-dated `student_class_enrollments` with Student/Class foreign keys, historical transfer fields, half-open interval semantics, model validation and PostgreSQL exclusion constraint preventing one Student's overlapping enrollments.
- Preserved boundaries: Student identity remains independent from class placement; no current-class field, overwrite/delete of history, homeroom, scheduling, attendance, or promotion workflow was added.
- Verification: targeted enrollment tests 3/3 with 9 assertions; full suite 30/30 with 150 assertions; Composer validation PASS; PostgreSQL migration batch 10 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 30 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S2-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S2-003`, which remains `NOT_STARTED` pending activation. Resume point: load homeroom assignment and scope-resolver authority.

## 2026-09-03 — IMP-S2-003 closeout
- Atomic step: added effective-dated `class_homeroom_assignments`, preserved assignment history, prevented overlapping assignments per class, and added a read-only resolver for active staff-to-class scope at a requested date.
- Preserved boundaries: no permanent `homeroom_staff_id`, RBAC policy mutation, handover exception workflow, scheduling, attendance, or production data change was added.
- Verification: targeted homeroom tests 3/3 with 7 assertions; full suite 33/33 with 157 assertions; Composer validation PASS; PostgreSQL migration batch 11 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 31 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S2-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S2-004`, which remains `NOT_STARTED` pending activation. Resume point: load Academic calendar event authority.

## 2026-09-03 — IMP-S2-004 closeout
- Atomic step: added `academic_calendar_events` with Academic Year scope, optional organizational-unit/class scope, explicit event type/session policy/workflow status, and start/end timestamps validated as a positive half-open interval.
- Preserved boundaries: no session generation, schedule rule, attendance, holiday automation, or policy approval workflow was added.
- Verification: targeted calendar-event tests 3/3 with 11 assertions; full suite 36/36 with 168 assertions; Composer validation PASS; PostgreSQL migration batch 12 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 32 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S2-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S3-001`, which remains `NOT_STARTED` pending activation. Resume point: load teaching-assignment authority.

## 2026-09-03 — IMP-S3-001 closeout
- Atomic step: added Academic `semesters` reference and canonical `teaching_assignments` linking semester, class, subject and teacher Staff with effective dates, workflow status, source/audit metadata, and interval validation.
- Preserved boundaries: no schedule rules, session generation, conflict engine, attendance, or teacher participation workflow was added.
- Verification: targeted teaching-assignment tests 3/3 with 13 assertions; full suite 39/39 with 181 assertions; Composer validation PASS; PostgreSQL migration batch 13 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 34 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S3-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S3-002`, which remains `NOT_STARTED` pending activation. Resume point: load schedule conflict/constraint authority.

## 2026-09-03 — IMP-S3-002 closeout
- Atomic step: added `schedule_rules` and `schedule_rule_week_numbers` with configurable recurrence types, weekday/time/effective intervals, and a deterministic structured checker for hard teacher/class conflicts using half-open time semantics.
- Preserved boundaries: no session generation, attendance, schedule-change apply workflow, location exclusivity, or conflict bypass was added.
- Verification: targeted schedule-rule tests 3/3 with 13 assertions; full suite 42/42 with 194 assertions; Composer validation PASS; PostgreSQL migration batch 14 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 36 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S3-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S3-003`, which remains `NOT_STARTED` pending activation. Resume point: load class-session generation and calendar-check authority.

## 2026-09-03 — IMP-S3-003 closeout
- Atomic step: added `class_sessions` with schedule/teaching/class/subject snapshots, lifecycle/source fields, idempotent `(schedule_rule_id, planned_start_at)` uniqueness, calendar `BLOCK` evaluation, and deterministic scheduled-session generation.
- Preserved boundaries: no participant snapshots, attendance, substitution, schedule-change workflow, or location policy was added.
- PostgreSQL initially exposed a self-referential FK ordering defect for `rescheduled_from_session_id`; the minimal fix adds that FK after table creation. Migration batch 15 then passed successfully.
- Verification: targeted session-generator tests 3/3 with 13 assertions; full suite 45/45 with 207 assertions; Composer validation PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 37 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S3-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S3-004`, which remains `NOT_STARTED` pending activation. Resume point: load session participant snapshot authority.

## 2026-09-03 — IMP-S3-004 closeout
- Atomic step: added `session_student_participants` with canonical Session/Student FKs, expected/removed lifecycle fields, participant basis, required flag, removal metadata, and unique session+student identity.
- Added idempotent `SessionParticipantSnapshotter` that snapshots Students with ACTIVE class enrollment at session time; attendance remains a later consumer of the participant FK.
- Verification: targeted participant snapshot tests 3/3 with 11 assertions; full suite 48/48 with 218 assertions; Composer validation PASS; PostgreSQL migration batch 16 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 38 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S3-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S3-005`, which remains `NOT_STARTED` pending activation. Resume point: load extra/ad-hoc session authority.

## 2026-09-03 — IMP-S3-005 closeout
- Atomic step: added transactional `ExtraSessionCreator` for `EXTRA`/`AD_HOC` sessions, with active class/teacher occupancy checks, structured `ScheduleConflictException`, explicit planned lifecycle, and no normal conflict bypass.
- Preserved boundaries: no schema change, participant/attendance workflow, substitution/swap/reschedule, or location exclusivity policy was added.
- Verification: targeted extra-session tests 3/3 with 8 assertions; full suite 51/51 with 226 assertions; Composer validation PASS; project structure and foundation verification PASS.
- No PostgreSQL migration was needed and no staging/production operation was performed.
- Updated task queue: `IMP-S3-005` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S4-001`, which remains `NOT_STARTED` pending activation. Resume point: load teacher participation/obligation authority.

## 2026-09-03 — IMP-S4-001 closeout
- Atomic step: added `session_teacher_participations` with PRIMARY/SUBSTITUTE roles, obligation type, expected/removed lifecycle, nullable attendance fields, removal/reason metadata, and session+teacher uniqueness.
- Added idempotent `TeacherParticipationRecorder` for PRIMARY obligations derived from the TeachingAssignment; official teacher attendance remains policy-dependent and untouched.
- Verification: targeted teacher-participation tests 3/3 with 15 assertions; full suite 54/54 with 241 assertions; Composer validation PASS; PostgreSQL migration batch 17 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 39 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S4-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S4-002`, which remains `NOT_STARTED` pending activation. Resume point: load substitution workflow authority.

## 2026-09-03 — IMP-S4-002 closeout
- Atomic step: added audited `schedule_changes` substitution records and transactional `SubstitutionService`; replacement teacher conflicts are rejected before mutation, while original PRIMARY obligation is preserved and EXPECTED SUBSTITUTE participation is added.
- Preserved boundaries: no swap, reschedule, cancellation, official teacher attendance, or conflict bypass was added.
- Verification: targeted substitution tests 3/3 with 13 assertions; full suite 57/57 with 254 assertions; Composer validation PASS; PostgreSQL migration batch 18 PASS; migration status/db inspection PASS; structure and foundation verification PASS.
- PostgreSQL local database now reports 40 tables. No staging/production operation was performed.
- Updated task queue: `IMP-S4-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S4-003`, which remains `NOT_STARTED` pending activation. Resume point: load swap workflow authority.

## 2026-09-03 — IMP-S4-003 closeout
- Atomic step: added transactional `SwapService` that validates both resulting teacher assignments before applying one audited SWAP change, preserves both PRIMARY obligations, and adds reciprocal EXPECTED SUBSTITUTE delivery participations.
- Preserved boundaries: no reschedule, cancellation, official teacher attendance, or conflict bypass was added.
- Verification: targeted swap tests 2/2 with 8 assertions; full suite 59/59 with 262 assertions; Composer validation PASS; project structure and foundation verification PASS.
- No PostgreSQL migration was needed and no staging/production operation was performed.
- Updated task queue: `IMP-S4-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S4-004`, which remains `NOT_STARTED` pending activation. Resume point: load reschedule/time-change authority.

## 2026-09-03 — IMP-S4-004 closeout
- Atomic step: added transactional `RescheduleService` for RESCHEDULE/TIME_CHANGE, validating target class/teacher occupancy before mutation, preserving source session as `RESCHEDULED`, and creating lineage-linked replacement sessions plus audited schedule changes.
- Preserved boundaries: no cancellation, attendance, or conflict bypass was added.
- Verification: targeted reschedule tests 3/3 with 9 assertions; full suite 62/62 with 271 assertions; Composer validation PASS; project structure and foundation verification PASS.
- No PostgreSQL migration was needed and no staging/production operation was performed.
- Updated task queue: `IMP-S4-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S4-005`, which remains `NOT_STARTED` pending activation. Resume point: load cancellation workflow authority.

## 2026-09-03 — IMP-S4-005 closeout
- Atomic step: added audited `CancellationService` that only cancels future active sessions, preserves the class-session row, marks it `CANCELLED`, and records an applied `CANCELLATION` schedule change.
- Preserved boundaries: completed/past sessions are rejected; no deletion, attendance, communication announcement, or period lock was added.
- Verification: targeted cancellation tests 2/2 with 5 assertions; full suite 64/64 with 276 assertions; Composer validation PASS; project structure and foundation verification PASS.
- No PostgreSQL migration was needed and no staging/production operation was performed.
- Updated task queue: `IMP-S4-005` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S4-006`, which is `BLOCKED_POLICY`. Resume point: await approved teacher-attendance policy; no implementation should start before activation.

## 2026-09-03 — IMP-S4-006 closeout
- Atomic step: added `TeacherAttendanceService` for effective Wali Kelas-scoped teacher attendance input, allowing only `PRESENT` or `ABSENT`, preserving class-session status, and allowing audited corrections with a reason.
- Preserved boundaries: no teacher self-confirmation, late/unavailable/no-response statuses, KPI activation, period lock, or session-status transition was added.
- Verification: targeted teacher-attendance tests 3/3 with 5 assertions; full suite 67/67 with 281 assertions; no database migration was needed and no staging/production operation was performed.
- Updated task queue: `IMP-S4-006` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S5-001`. Resume point: load the student-attendance draft-entry context.

## 2026-09-03 — IMP-S5-001 closeout
- Atomic step: added contract-defined `student_attendance` schema/model and `StudentAttendanceDraftService` with effective Wali Kelas scope, incomplete DRAFT support, per-participant uniqueness, versioning and audit.
- Preserved boundaries: missing attendance is not ABSENT; finalization, period lock, permission validation and KPI remain separate/deferred.
- Verification: targeted student-attendance draft tests 3/3 with 16 assertions; full suite 70/70 with 297 assertions; Composer validation and PHP syntax checks PASS.
- Migration-backed SQLite tests PASS; local PostgreSQL migrate command was not run because sandbox TCP access was denied. No staging/production operation was performed.
- Updated task queue: `IMP-S5-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S5-002`. Resume point: load finalization authority and deterministic validation requirements.

## 2026-09-03 — IMP-S5-002 closeout
- Atomic step: added `StudentAttendanceFinalizer` for atomic DRAFT → VALIDATED transition, effective Wali Kelas authorization, required-participant completeness, controlled status, optimistic version validation, audit, and eligible session completion.
- Preserved boundaries: cancelled/rescheduled sessions are rejected; permission-reference consistency, period lock, post-lock correction and KPI remain separate/deferred.
- Verification: targeted finalizer tests 5/5 with 10 assertions; full suite 75/75 with 307 assertions; no migration or staging/production operation was needed.
- Updated task queue: `IMP-S5-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S5-003`. Resume point: load permission reference consistency context.

## 2026-09-03 — IMP-S5-003 policy checkpoint
- No runtime implementation performed because `POL-PERM-001` remains POLICY_PENDING, with related overlap and approval policies also unresolved.
- Preserved safe boundary: no permission linkage, permission-events table, overlap rule, approval state or attendance behavior was guessed.
- Updated task queue and routing: `IMP-S5-003` is `BLOCKED_POLICY` at 0%. Resume point: record approved permission policy and minimum runtime contract.

## 2026-09-03 — IMP-S5-003 closeout
- Owner policy recorded: Wali Kelas may directly select per-session `IZIN` without a permission event; mid-session arrival is `PRESENT` with optional notes; Wali Kelas owns the input.
- Atomic step: added `IZIN` to controlled student attendance finalization and acceptance coverage for optional permission reference and session-local notes.
- Preserved boundaries: no permission-events table, cross-session propagation, overlap calculation, or approval workflow was introduced.
- Verification: targeted finalizer tests 5/5 with 13 assertions; full suite 75/75 with 310 assertions; no migration or staging/production operation performed.
- Updated task queue: `IMP-S5-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S5-004`. Resume point: load UI task context and inspect existing presentation layer only.

## 2026-09-03 — IMP-S5-004 technical checkpoint
- No runtime UI implementation performed because the application has no authenticated User → Staff identity resolver, middleware-protected attendance route, or existing presentation entrypoint.
- Safe boundary preserved: no browser-supplied `staff_id` and no UI mutation endpoint was introduced, avoiding an RBAC/data-scope bypass.
- Updated task queue and routing: `IMP-S5-004` is `BLOCKED_TECHNICAL` at 0%. Resume point: implement/approve the identity resolver and authorized route boundary.

## 2026-09-03 — IMP-S5-004 identity boundary checkpoint
- Atomic step: added explicit `user_staff_links`, User/Staff relations, and `WaliKelasContextResolver` requiring effective WALI_KELAS role plus effective homeroom assignment for the session date.
- Preserved boundaries: no client-supplied staff identity, attendance mutation route, UI, or unrelated auth flow was added.
- Verification: targeted resolver tests 2/2 with 3 assertions; full suite 77/77 with 313 assertions; Composer validation and PHP syntax checks PASS.
- Updated task queue: `IMP-S5-004` is `IN_PROGRESS` at 25%. Resume point: implement the authorized responsive attendance route/view.

## 2026-09-03 — IMP-S5-004 closeout
- Atomic step: added authenticated attendance routes, `StudentAttendanceController`, and responsive Blade UI for roster display, per-student status/notes draft save, and finalization request.
- Backend boundary: controller resolves User → Staff and Wali Kelas scope server-side; no client-supplied staff identity is accepted.
- Verification: targeted UI tests 2/2 with 8 assertions; full suite 79/79 with 321 assertions; Composer validation, PHP syntax, and route listing PASS.
- No migration or staging/production operation was performed.
- Updated task queue: `IMP-S5-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S5-005`. Resume point: load attendance DQ completeness context.

## 2026-09-03 — IMP-S5-005 closeout
- Atomic step: added read-only `StudentAttendanceCompletenessChecker` reporting missing and unresolved required participant attendance, excluding cancelled/rescheduled sessions.
- Preserved boundaries: missing attendance is never converted to ABSENT; no attendance mutation, alert persistence, notification, or KPI aggregation was added.
- Verification: targeted DQ tests 2/2 with 6 assertions; full suite 81/81 with 327 assertions.
- Updated task queue: `IMP-S5-005` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S6-001`. Resume point: load open-period correction authority.

## 2026-09-03 — IMP-S6-001 closeout
- Atomic step: added `StudentAttendanceCorrectionService` for effective Wali Kelas correction of VALIDATED attendance, requiring reason, deterministic integrity checks, version check, version increment and audit.
- Preserved boundaries: no period lock, post-lock correction request, session status mutation, KPI, or unrelated attendance behavior was added.
- Verification: targeted correction tests 3/3 with 6 assertions; full suite 84/84 with 333 assertions; Composer validation and PHP syntax checks PASS.
- Updated task queue: `IMP-S6-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S6-002`. Resume point: load period-lock prerequisites and scope authority.

## 2026-09-03 — IMP-S6-002 policy checkpoint
- No runtime implementation performed because `POL-SOP-002` lock timing, `POL-SOP-003` lock actor, and `POL-SOP-004` post-lock correction approver remain POLICY_PENDING.
- The SOP class × calendar month/date-range recommendation was not treated as an approval.
- Updated task queue and routing: `IMP-S6-002` is `BLOCKED_POLICY` at 0%. Resume point: record approved lock grain/timing, actor, prerequisites and post-lock approver.

## 2026-09-03 — IMP-S6-002 closeout
- Owner policy recorded: lock per class per calendar month, 15 days after month end, by Waka Akademik; post-lock correction approved by Waka Akademik; Super Admin has an explicit future override.
- Atomic step: added `attendance_period_locks` and `AttendancePeriodLockService` with completeness prerequisite, deadline enforcement, Waka authorization and audit.
- Preserved boundaries: no post-lock correction or Super Admin override mutation was added.
- Verification: targeted lock tests 4/4 with 8 assertions; full suite 88/88 with 341 assertions; Composer validation and PHP syntax checks PASS.
- Updated task queue: `IMP-S6-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S6-003`. Resume point: load post-lock correction request/review authority.

## 2026-09-03 — IMP-S6-003 closeout
- Owner policy recorded: post-lock correction is Wali Kelas request → Waka Akademik review/apply; Super Admin has explicit override at any time with reason/version/audit.
- Atomic step: added targeted post-lock correction request/review/apply using existing correction-request foundation, plus explicit Super Admin override; no whole-period unlock.
- Verification: targeted correction tests 4/4 with 10 assertions; full suite 92/92 with 351 assertions; Composer validation and PHP syntax checks PASS.
- Updated task queue: `IMP-S6-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S6-004`. Resume point: load historical homeroom handover authority.

## 2026-09-03 — IMP-S6-004 closeout
- Atomic step: added `HistoricalHomeroomHandoverService` for explicit `ADMIN_AKADEMIK` permission-based completion of historical attendance using the existing finalizer, preserving effective historical homeroom context.
- Preserved boundaries: new Wali Kelas receives no automatic historical rights; locked periods remain on the post-lock correction path; reason, actor, permission and handover action are audited.
- Verification: targeted handover tests 4/4 with 7 assertions; full suite 96/96 with 358 assertions; no migration or staging/production operation was performed.
- Updated task queue: `IMP-S6-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S6-005`. Resume point: load admin attendance exception/completeness view authority.

## 2026-09-03 — IMP-S6-005 closeout
- Atomic step: added a read-only admin attendance exception view backed by `StudentAttendanceCompletenessChecker`, listing missing and unresolved required attendance while excluding cancelled/rescheduled sessions.
- Backend boundary: only effective `ADMIN_AKADEMIK` may open the view; no attendance mutation, finding persistence, notification or KPI aggregation was added.
- Verification: targeted UI tests 2/2 with 5 assertions; full suite 98/98 with 363 assertions; no migration or staging/production operation was performed.
- Updated task queue: `IMP-S6-005` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S7-001`. Resume point: load semester-grade schema/provenance authority.

## 2026-09-03 — IMP-S7-001 closeout
- Atomic step: added canonical `semester_subject_grades` schema/model and `SemesterGradeEntryService` for DRAFT persistence at Student × Subject × Semester grain with score range, source and nullable teaching-assignment provenance.
- Preserved boundaries: missing grade is not zero; explicit zero is valid; version/audit are recorded; official grade entry/finalization/lock authority remains POLICY_PENDING.
- Verification: targeted grade tests 6/6 with 12 assertions; full suite 104/104 with 375 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S7-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S7-002`. Resume point: load grade completeness authority.

## 2026-09-03 — IMP-S7-002 closeout
- Atomic step: added read-only `SemesterGradeCompletenessService` reporting expected, available and missing students per active subject assignment for a semester.
- Preserved boundaries: expected students use effective enrollment intervals; transfers count once; missing grade is not zero; no grade mutation, finding persistence, finalization, lock or officialization was added.
- Verification: targeted completeness tests 2/2 with 8 assertions; full suite 106/106 with 383 assertions; no migration or staging/production operation was performed.
- Updated task queue: `IMP-S7-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S7-003`. Resume point: load grade correction audit/version authority.

## 2026-09-03 — IMP-S7-003 closeout
- Atomic step: added request-only `SemesterGradeCorrectionRequestService` using the existing correction request and append-only audit foundations, preserving expected version and old/new requested values.
- Preserved boundaries: correction requests do not apply grade changes or change workflow status; approval/finalization/lock authority remains POLICY_PENDING.
- Verification: targeted correction tests 4/4 with 9 assertions; full suite 110/110 with 392 assertions; no migration or staging/production operation was performed.
- Updated task queue: `IMP-S7-003` is `DONE` at 100%; `NEXT_ACTION.md` points to blocked `IMP-S7-004`. Resume point: obtain approved grade finalization/lock policy before implementation.

## 2026-09-03 — IMP-S7-004 closeout
- Owner policy recorded: Wali Kelas inputs/checks; Waka Akademik approves and locks per Student × Subject × Semester after score completeness.
- Atomic step: added `SemesterGradeFinalizationService` with `DRAFT → CHECKED → LOCKED`, effective Wali Kelas/class/student scope, Waka approval/lock authority, version increments and append-only audit.
- Preserved boundaries: no report/transcript publication, bulk lock or correction approval workflow was added.
- Verification: targeted finalization tests 3/3 with 9 assertions; full suite 113/113 with 401 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S7-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S8-001`. Resume point: load report-card readiness authority.

## 2026-09-03 — IMP-S8-001 closeout
- Atomic step: added read-only `ReportCardReadinessService` for Student × Semester × Class context, requiring locked non-null semester grades and every calendar-month attendance period to be locked.
- Preserved boundaries: readiness returns explicit reasons; missing grades are not inferred as zero; no report artifact, snapshot, publication or KPI was added.
- Verification: targeted readiness tests 3/3 with 11 assertions; full suite 116/116 with 412 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S8-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S8-002`. Resume point: load structured report-card snapshot authority.

## 2026-09-03 — IMP-S8-002 closeout
- Atomic step: added report-card identity/version, subject-line and attendance-line snapshot tables/models plus `ReportCardDraftService` for readiness-gated DRAFT generation.
- Preserved boundaries: source grade and attendance facts are not edited; snapshots are immutable and new generations create new versions; review/approval/publication/PDF remains separate.
- Verification: targeted draft tests 3/3 with 10 assertions; full suite 119/119 with 422 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S8-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S8-003`. Resume point: load homeroom report-note authority.

## 2026-09-03 — IMP-S8-003 closeout
- Atomic step: added `report_card_notes` and Wali Kelas-scoped `ReportCardNoteService` for optional draft notes with audit and `approved_for_parent_report=false` by default.
- Preserved boundaries: notes are editable only on DRAFT versions; no note-mandatory assumption, parent exposure, approval or publication was introduced.
- Verification: targeted note tests 2/2 with 6 assertions; full suite 121/121 with 428 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S8-003` is `DONE` at 100%; `NEXT_ACTION.md` points to blocked `IMP-S8-004`. Resume point: obtain report review/approval/publication policy.

## 2026-09-03 — IMP-S8-004 closeout
- Owner policy recorded: Wali Kelas reviews; Waka Akademik approves and publishes; Kepala Sekolah and Wali Kelas are signatories.
- Atomic step: added report version transition service `DRAFT → REVIEWED → APPROVED → PUBLISHED`, approval/publication audit, parent-note approval on publication, and immutable signatory snapshots.
- Preserved boundaries: no bulk publication, transcript publication or mutable published snapshot was added.
- Verification: targeted publication tests 2/2 with 8 assertions; full suite 123/123 with 436 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S8-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S8-005`. Resume point: load academic history view authority.

## 2026-09-03 — IMP-S8-005 closeout
- Atomic step: added read-only `AcademicHistoryService` derived from locked canonical semester subject grades, ordered by semester and subject with provenance retained.
- Preserved boundaries: unlocked grades, report PDFs and transcript artifacts are not history sources; historical facts remain queryable after lifecycle changes.
- Verification: targeted history tests 2/2 with 7 assertions; full suite 125/125 with 443 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S8-005` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S8-006`. Resume point: load transcript snapshot authority.

## 2026-09-03 — IMP-S8-006 closeout
- Atomic step: added `academic_transcripts`, `academic_transcript_versions`, and `academic_transcript_lines`, plus `AcademicTranscriptDraftService` derived from locked canonical semester-subject grades.
- Preserved boundaries: transcript drafts never read report PDFs; repeated subjects across semesters remain separate; versions and lines are append-only; approval, publication, PDF, and signatory policy remain out of scope.
- Verification: targeted transcript tests 2/2 with 9 assertions; full suite 127/127 with 452 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S8-006` is `DONE` at 100%; `NEXT_ACTION.md` points to policy-blocked `IMP-S8-007`.

## 2026-09-03 — IMP-S8-007 closeout
- Approved policy: Wali Kelas reviews; Waka Akademik approves and publishes; Kepala Sekolah and Wali Kelas are signatories; Super Admin may create an audited revision at any time without mutating published history.
- Atomic step: added transcript `DRAFT → REVIEWED → APPROVED → PUBLISHED` transitions, immutable signatory snapshots, audit events, and Super Admin revision creation.
- Preserved boundaries: published transcript versions remain immutable; revisions create a new DRAFT version; no PDF, portal delivery, or report-card source coupling.
- Verification: targeted publication tests 3/3 with 11 assertions; full suite 130/130 with 463 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S8-007` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S9-001`.

## 2026-09-03 — IMP-S9-001 closeout
- Atomic step: added `AttendanceSemanticMetricsService` with transaction-grain eligible opportunities, status counts, physical presence, unexcused absence, and completeness rates.
- Preserved boundaries: cancelled/rescheduled sessions are excluded; missing or unresolved attendance is Data Quality, never counted as absence; no generic unofficial attendance percentage was introduced.
- Verification: targeted metrics test 1/1 with 7 assertions; full suite 131/131 with 470 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S9-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S9-002`.

## 2026-09-03 — IMP-S9-002 closeout
- Atomic step: added `SessionSemanticMetricsService` for counted, completed, open, cancelled, rescheduled-source, extra/ad-hoc, and completion-rate metrics.
- Preserved boundaries: cancelled sessions and rescheduled source sessions are excluded from the counted denominator; replacement sessions are counted once; extra/ad-hoc sessions remain explicitly identifiable.
- Verification: targeted session metrics test 1/1 with 7 assertions; full suite 132/132 with 477 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S9-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S9-003`.

## 2026-09-03 — IMP-S9-003 closeout
- Atomic step: added `GradeSemanticMetricsService` for locked-grade completeness, transaction-grain means, and per-student semester trends.
- Preserved boundaries: missing grades are not zero; only `LOCKED` grades enter official metrics; no GPA, KKM/mastery, remedial, ranking, or composite score was introduced.
- Verification: targeted grade metrics tests 2/2 with 8 assertions; full suite 134/134 with 485 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S9-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S9-004`.

## 2026-09-03 — IMP-S9-004 closeout
- Simplified approved role scope: Wali Kelas sees only assigned homeroom classes; Waka Akademik sees all Academic classes; no separate Admin dashboard role was added.
- Atomic step: added read-only Academic dashboard service, route/controller, responsive summary view, and backend role/scope tests using attendance and session semantic metrics.
- Preserved boundaries: role enforcement is backend-side; dashboard has no write actions, no sensitive audit exposure, and no new role inheritance.
- Verification: targeted dashboard tests 2/2 with 4 assertions; full suite 136/136 with 489 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S9-004` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S9-005`.

## 2026-09-03 — IMP-S9-004 review correction
- Corrected dashboard grade metrics to pass the selected class scope, aligned homeroom `effective_until` with exclusive-end semantics, and added controller date/semester UUID validation.
- Verification: dashboard + grade regression tests 5/5 with 13 assertions; full suite 137/137 with 490 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S9-004 second review correction
- Added selected-semester grade cards to the dashboard, rejected reversed date ranges, and evaluated Wali role/Staff-link scope at the requested period start.
- Verification: dashboard + grade regression tests 6/6 with 14 assertions; full suite 138/138 with 491 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S9-004 Waka dashboard cleanup
- Waka dashboard now lists only Academic classes with session activity in the selected period; historical class master rows without activity remain preserved but are omitted from the view.
- Verification: dashboard tests 4/4 with 6 assertions; full suite 138/138 with 491 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S9-005 closeout
- Atomic step: added CSV export for the Academic dashboard through `AcademicRoleDashboardService`, with explicit `academic.dashboard.export` permission and the same Wali/Waka scope enforcement.
- Preserved boundaries: export is read-only, formula-free beyond semantic services, and does not expose audit detail or sensitive fields.
- Verification: targeted export/dashboard tests 5/5 with 8 assertions; full suite 139/139 with 493 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S9-005` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S10-001`.

## 2026-09-03 — IMP-S9-005 export review correction
- Added semester/subject grade columns to CSV when a semester is selected, added explicit denial coverage without export permission, and added the dashboard export link.
- Verification: export/dashboard tests 6/6 with 9 assertions; full suite 140/140 with 494 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S9-005 export review correction
- Ensured a class still emits its attendance/session summary row when a selected semester has no grade metrics; grade columns remain empty.
- Verification: export/dashboard tests 6/6 with 11 assertions; full suite 140/140 with 496 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S10-001 closeout
- Atomic step: added shared `alert_rules`, `alerts`, and `alert_actions` infrastructure with versioned active rules, owner/severity/status/due/resolution fields, action history, audit events, active deduplication, and retained closed occurrences.
- Preserved boundaries: no Academic rule execution, student early-warning threshold, punishment/diagnosis, or composite risk score was introduced.
- Verification: targeted alert test 1/1 with 6 assertions; full suite 141/141 with 502 assertions; Composer validation and PHP syntax checks PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S10-001` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S10-002`.

## 2026-09-03 — IMP-S10-001 review correction
- Added PostgreSQL partial uniqueness for active alert deduplication, owner/`platform.alert.manage` authorization for transitions, immutable alert rules, and resolution metadata clearing on reopen.
- Verification: alert tests 3/3 with 11 assertions; full suite 143/143 with 507 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S10-001 second review correction
- Added cross-database `dedup_key` uniqueness, an explicit alert transition matrix, and `AlertRuleVersionService` so new immutable rule versions supersede older raisable versions.
- Added regression coverage for recurrence/reopen behavior, invalid transitions, manager permission, and rule version supersession.
- Verification: alert tests 6/6 with 14 assertions; full suite 146/146 with 510 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S10-001 final review correction
- Added explicit rule-version supersession linkage and `ALERT_RULE_VERSION_CREATED` audit coverage; aligned the manifest with cross-database `dedup_key` enforcement.
- Verification: alert tests 6/6 with 16 assertions; full suite 146/146 with 512 assertions; Composer validation and PHP syntax checks PASS.

## 2026-09-03 — IMP-S10-002 closeout
- Atomic step: added deterministic Academic DQ-001 missing-attendance evaluation through the canonical `StudentAttendanceCompletenessChecker`.
- Active identical findings are deduplicated through the shared alert service; repeated evaluation does not create a duplicate, and a cleared/not-applicable condition closes the retained alert occurrence.
- Preserved boundaries: owner resolution automation remains S10-003; no early-warning threshold, punishment/diagnosis, composite risk score, schema, or RBAC global policy change was introduced.
- Verification: targeted DQ test 1/1 with 3 assertions; full suite 147/147 with 515 assertions; PHP syntax checks and Composer validation PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S10-002` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S10-003`.

## 2026-09-03 — IMP-S10-002 review correction
- Added explicit service coverage proving cancelled sessions are `NOT_APPLICABLE` and do not raise a missing-attendance alert.
- Verification: targeted DQ test 2/2 with 5 assertions; full suite 148/148 with 517 assertions; no implementation regression found.

## 2026-09-03 — IMP-S10-003 review correction
- Deferred owner resolution until DQ-001 must raise an alert or close an existing active alert; complete/not-applicable sessions without an owner now return safely without raising authorization errors.
- Added regression coverage for a cancelled session without owner identity.
- Verification: targeted DQ test 3/3 with 7 assertions; full suite 149/149 with 519 assertions; PHP syntax checks PASS.

## 2026-09-03 — IMP-S10-003 atomic checkpoint: owner resolution
- Added `AcademicAlertOwnerResolver` to resolve the effective Wali Kelas user from the session date, active class assignment, active WALI_KELAS role, and User/Staff identity link.
- Integrated DQ-001 evaluation with the resolver so alert ownership is derived by the application boundary rather than supplied by the caller.
- Missing effective owner fails closed with `AuthorizationException`; owner-aware deduplication and deterministic auto-resolution remain unfinished.
- Verification: targeted DQ test 2/2 with 5 assertions; full suite 148/148 with 517 assertions; PHP syntax checks PASS.

## 2026-09-03 — IMP-S10-003 atomic checkpoint: owner-aware deduplication
- Updated shared alert raising so an active identical condition remains one occurrence while its owner is reassigned when the effective Wali Kelas changes; reassignment is audit-logged.
- Added coverage proving owner change does not create a duplicate alert.
- Remaining scope: deterministic auto-resolution; early-warning rules remain inactive.
- Verification: targeted DQ test 4/4 with 10 assertions; full suite 150/150 with 522 assertions; PHP syntax and Composer validation PASS.

## 2026-09-03 — IMP-S10-003 review correction
- Added an explicit assertion that owner reassignment emits exactly one `ALERT_OWNER_REASSIGNED` audit event.
- Verification: targeted DQ test 4/4 with 11 assertions; full suite 150/150 with 523 assertions.

## 2026-09-03 — IMP-S10-003 closeout
- Completed deterministic Academic alert operations: effective Wali Kelas owner resolution, active owner-aware deduplication with audited reassignment, and auto-closing of cleared attendance conditions.
- Preserved boundaries: student early-warning thresholds remain policy-blocked; no schema, Shared Core/RBAC global policy, punishment/diagnosis, or composite risk score was introduced.
- Verification: targeted DQ test 5/5 with 13 assertions; full suite 151/151 with 525 assertions; PHP syntax checks and Composer validation PASS; no staging/production operation was performed.
- Updated task queue: `IMP-S10-003` is `DONE` at 100%; `NEXT_ACTION.md` now points to `IMP-S11-001`.

## 2026-09-03 — IMP-S11-001 atomic checkpoint: import persistence infrastructure
- Change class: `DATABASE_GLOBAL`; created additive import batch/file/row/error/mapping/lineage tables with checksum, raw payload, accounting statuses, quarantine/error support, and target traceability.
- Added six Eloquent models and relationships; no importer, canonical writes, dry-run, identity matching, or approval workflow was introduced.
- Verification: targeted import infrastructure test 2/2 with 7 assertions; full suite pending at this checkpoint.
- Updated task queue: `IMP-S11-001` is `IN_PROGRESS` at 25%.

## 2026-09-03 — IMP-S11-001 verification checkpoint
- Full application suite passed: 153/153 tests with 532 assertions.
- Migration status smoke check could not connect to local PostgreSQL because sandbox networking denied `127.0.0.1:5432`; no database change occurred. SQLite-backed feature tests exercised the new tables successfully.

## 2026-09-03 — IMP-S11-001 PostgreSQL status verification
- Read-only `php artisan migrate:status` succeeded with elevated permission.
- Migration `2026_09_03_000028_create_import_infrastructure_tables` is `Pending`, as are migrations 18–27; no migration was executed and no database state changed.

## 2026-09-03 — IMP-S11-001 migration safety correction
- Replaced import infrastructure cascade deletes with `restrictOnDelete` to preserve retained source facts, errors, mappings, and lineage.
- Added regression coverage proving a batch with source rows cannot be deleted.
- Verification: targeted import test 3/3 with 8 assertions; full suite 154/154 with 533 assertions; PHP syntax and Composer validation PASS.

## 2026-09-03 — IMP-S11-001 integrity correction
- Added composite foreign keys and regression coverage preventing cross-batch file/row/lineage combinations.
- Verification: targeted import test 4/4 with 10 assertions; full suite 155/155 with 535 assertions; migration PHP syntax PASS.

## 2026-09-03 — IMP-S11-001 atomic checkpoint: source intake
- Added `ImportIntakeService` to create auditable batches and register preserved source-file metadata with checksum normalization and migration-contract granularity validation.
- No parser, row processor, identity matching, dry-run, reconciliation, approval, or canonical write path was introduced.
- Verification: targeted import test 6/6 with 14 assertions; full suite 157/157 with 539 assertions; PHP syntax and Composer validation PASS.
- Updated task queue: `IMP-S11-001` is `IN_PROGRESS` at 40%.

## 2026-09-03 — IMP-S11-001 atomic checkpoint: batch lifecycle guard
- Added ordered import-batch transitions and prevented source-file intake after the batch leaves `DRAFT`.
- Corrected `createBatch()` to refresh the database-default `DRAFT` status before lifecycle checks; added coverage for invalid late intake.
- Verification: targeted import test 7/7 with 16 assertions; full suite 158/158 with 541 assertions; PHP syntax and Composer validation PASS.

## 2026-09-03 — IMP-S11-001 atomic checkpoint: row ingestion
- Added validated raw-row ingestion for `RECEIVED/PROFILED/STAGED` batches, cross-batch file protection, and idempotent rerun behavior for identical source content.
- Changed duplicate source content is rejected; closed/unsupported batch states cannot accept rows.
- Verification: targeted import test 9/9 with 20 assertions; full suite 160/160 with 545 assertions; PHP syntax and Composer validation PASS.

## 2026-09-03 — IMP-S11-001 review correction: transition stale state
- Refreshed batch state before transition validation so a stale caller model does not cause a false rejection; the locked transaction check remains authoritative.
- Added coverage for a valid transition requested through a stale batch object.
- Verification: targeted import test 9/9 with 21 assertions; full suite 160/160 with 546 assertions.
- Updated task queue: `IMP-S11-001` is `IN_PROGRESS` at 60%.

## 2026-09-03 — IMP-S11-001 review correction: row retry safety
- Refreshed batch/file state before row guards and handled unique-constraint races by reusing an identical existing row while rejecting changed content.
- Added stale-model coverage; no migration or canonical write behavior changed.
- Verification: targeted import test 9/9 with 20 assertions; full suite 160/160 with 545 assertions; PHP syntax and Composer validation PASS.

## 2026-09-03 — IMP-S11-001 atomic checkpoint: row outcome accounting
- Added pending-only row outcome accounting for `IMPORTED`, `REJECTED`, `QUARANTINED`, `DUPLICATE`, and `EXCLUDED`; rejection/quarantine require a persisted error, and outcome counters update atomically with `accounted_at`.
- Added coverage for quarantine/error atomicity, required rejection errors, and protection against re-accounting an already-accounted row.
- Verification: targeted import test 11/11 with 26 assertions; full suite 162/162 with 551 assertions; PHP syntax and Composer validation PASS.
- Updated task queue: `IMP-S11-001` is `IN_PROGRESS` at 75%.
- Resume point: reconciliation finalizer, source-total accounting, and batch close guard.

## 2026-09-04 — IMP-S11-001 atomic checkpoint: reconciliation finalizer and close guard
- Added reconciliation finalization for `DRY_RUN` batches: all rows must be accounted and outcome counters must equal `source_total` before entering `RECONCILED`.
- Added a close guard so `IMPORTED → CLOSED` revalidates the same accounting invariant; no canonical writes or database migration execution occurred.
- Verification: targeted import test 13/13 with 29 assertions; full suite 164/164 with 554 assertions; PHP syntax and Composer validation PASS.
- Updated task queue: `IMP-S11-001` is `IN_PROGRESS` at 90%.
- Resume point: final closeout review, migration readiness, and completion status; migration remains pending until explicit approval.

## 2026-09-04 — IMP-S11-001 closeout
- Final review confirmed the import persistence, row outcome accounting, reconciliation finalizer, and close guard deliverable is complete.
- Updated task queue and task context to `DONE` at 100%; S11-002 is now the next task and remains inactive until explicitly started.
- Final verification remains targeted 13/13 with 29 assertions, full suite 164/164 with 554 assertions, PHP syntax and Composer validation PASS.
- Migration readiness remains `NOT_READY`: migrations 18–28 are pending locally and no database migration was executed.

## 2026-09-04 — IMP-S11-002 atomic checkpoint: dry-run entry guard
- Activated S11-002 and added a server-side guard for `VALIDATED → DRY_RUN`: pending rows and rows with canonical targets block entry.
- No migration, identity matching, canonical write, or import execution was introduced.
- Verification: targeted import test 14/14 with 32 assertions; full suite 165/165 with 557 assertions; PHP syntax and Composer validation PASS.
- Updated task queue: `IMP-S11-002` is `IN_PROGRESS` at 25%.
- Resume point: dry-run preview output and identity-review candidate preparation.

## 2026-09-04 — IMP-S11-002 atomic checkpoint: dry-run preview summary
- Added read-only `dryRunPreview()` with deterministic source/row outcome/error/canonical-target counts and reconciliation readiness.
- Verification: targeted import test 15/15 with 39 assertions; PHP syntax PASS; prior full suite 165/165 with 557 assertions remains valid for the isolated read-only addition.
- Updated task queue: `IMP-S11-002` is `IN_PROGRESS` at 40%.
- Resume point: identity-review candidate preparation.

## 2026-09-04 — IMP-S11-002 atomic checkpoint: identity-review candidates
- Added read-only identity-review candidate preparation for unresolved `PENDING`/`QUARANTINED` rows, preserving raw payload and error evidence.
- Candidates are explicitly human-review-only (`auto_match=false`); no Student creation, canonical target assignment, or automatic merge was introduced.
- Verification: targeted import test 16/16 with 46 assertions; PHP syntax PASS.
- Updated task queue: `IMP-S11-002` is `IN_PROGRESS` at 60%.
- Resume point: reviewed identity resolution and reconciliation completion.

## 2026-09-04 — IMP-S11-002 atomic checkpoint: reviewed identity mapping
- Added explicit human-reviewed mapping decisions (`MATCHED`, `REJECTED`, `QUARANTINED`) with reviewer, confidence, evidence, and target validation; decisions are allowed only in `DRY_RUN`.
- No automatic merge, Student creation, canonical Student mutation, or row-lineage propagation was introduced.
- Verification: targeted import test 17/17 with 50 assertions; PHP syntax PASS.
- Updated task queue: `IMP-S11-002` is `IN_PROGRESS` at 80%.
- Resume point: mapping-to-row propagation and reconciliation completion.

## 2026-09-04 — IMP-S11-002 atomic checkpoint: mapping-to-row propagation
- Added same-batch/source-key validated propagation from reviewed `MATCHED` mappings to import-row target metadata and one idempotent `IMPORTED_FROM` lineage record.
- No canonical Student creation or mutation was introduced; repeated propagation is safe and does not duplicate lineage.
- Verification: targeted import test 18/18 with 53 assertions; full suite 169/169 with 578 assertions; PHP syntax PASS.
- Updated task queue: `IMP-S11-002` is `IN_PROGRESS` at 90%.
- Resume point: reconciliation completion.

## 2026-09-04 — IMP-S11-002 closeout
- Completed dry-run guard/preview, human identity-review candidate and decision flow, import metadata propagation, idempotent lineage, and reconciliation completion guard.
- Reconciliation now requires complete row accounting and zero pending identity mappings before `RECONCILED`.
- Final verification: targeted import test 19/19 with 54 assertions; full suite 170/170 with 579 assertions; PHP syntax PASS.
- Updated task queue and task context to `IMP-S11-002` `DONE` at 100%; S11-003 is next and remains inactive until explicitly started.
- No migration was executed; pending S11-001 database deployment still requires explicit approval.

## 2026-09-04 — IMP-S11-003 atomic checkpoint: legacy source inventory report
- Activated S11-003 and added a read-only source inventory report from registered import files.
- Daily/monthly granularity is preserved and reported; session expansion is explicitly disallowed.
- No legacy table was created because actual source inventory evidence is not present yet.
- Verification: targeted import test 20/20 with 59 assertions; PHP syntax PASS; last full suite 170/170 with 579 assertions.
- Updated task queue: `IMP-S11-003` is `IN_PROGRESS` at 25%.
- Resume point: evidence review and conditional legacy-grain persistence design.

## 2026-09-04 — IMP-S11-003 atomic checkpoint: conditional legacy-grain design
- Reviewed available evidence: no actual daily/monthly legacy source sample or field dictionary is present.
- Documented conditional candidate storage, lineage requirements, and creation gates; no legacy migration/table was invented or created.
- Verification: design review against migration/schema contracts; prior targeted source-inventory test 20/20 with 59 assertions and PHP syntax PASS.
- Updated task queue: `IMP-S11-003` is `IN_PROGRESS` at 50%.
- Resume point: obtain/profile an actual daily/monthly source sample and approve its field dictionary before schema implementation.

## 2026-09-04 — IMP-S11-003 atomic checkpoint: synthetic profiling samples
- Added anonymized daily and monthly CSV fixtures plus README for safe profiling and contract tests.
- Daily sample has 5 data rows; monthly sample has 4 data rows; each preserves its source grain and explicitly avoids session expansion.
- Synthetic fixtures are not production evidence; legacy migration/schema creation remains conditional on approved field semantics.
- Verification: headers, row counts, and SHA-256 checksums reviewed.
- Updated task queue: `IMP-S11-003` is `IN_PROGRESS` at 65%.
- Resume point: profile/approve production-equivalent fields before legacy migration design.

## 2026-09-04 — IMP-S11-003 atomic checkpoint: fixture dictionary review
- Reviewed and approved the field dictionary for synthetic fixture profiling and import-contract tests only.
- Production field semantics, legacy table schema, and migration deployment remain unapproved pending institutional source confirmation.
- Updated task queue: `IMP-S11-003` is `IN_PROGRESS` at 90%.
- Resume point: confirm production-equivalent source fields before legacy migration creation.

## 2026-09-04 — IMP-S11-003 atomic checkpoint: production field confirmation checklist
- Added a checklist for confirming stable source keys, daily/monthly grain, status meanings, aggregate denominator, ownership, period, and checksum.
- All production decisions remain `PENDING_CONFIRMATION`; synthetic fixtures do not authorize a production migration.
- Updated task queue: `IMP-S11-003` is `IN_PROGRESS` at 95%.
- Resume point: fill the checklist from an approved production-equivalent source.

## 2026-09-04 — IMP-S11-003 closeout
- Closed S11-003 at 100% for source inventory, conditional legacy-grain design, synthetic samples, fixture dictionary, and production confirmation checklist.
- Legacy table creation remains deferred until production evidence and field semantics are confirmed; no schema was inferred from synthetic samples.
- Updated task queue and task context to `IMP-S11-003` `DONE`; S11-004 is next and remains inactive until explicitly started.

## 2026-09-04 — IMP-S11-004 atomic checkpoint: canonical import preflight
- Activated S11-004 and added a read-only canonical import preflight gate for reconciled batches.
- The gate reports blockers for non-reconciled status, accounting mismatch, pending rows/mappings, imported rows without canonical targets, and missing lineage.
- No canonical master/history record or database migration was written.
- Verification: targeted import test 22/22 with 66 assertions; PHP syntax PASS.
- Updated task queue: `IMP-S11-004` is `IN_PROGRESS` at 25%.
- Resume point: controlled canonical master/history import execution.

## 2026-09-04 — IMP-S11-004 atomic checkpoint: bounded Student master executor
- Added a transaction-scoped executor for reconciled imported rows targeting existing Students, with an allowlist of non-identity fields, version increment, and append-only import audit.
- Permanent `student_code` is never overwritten; Student creation, status/history mutation, and broader canonical import remain out of scope.
- Verification: targeted import test 23/23 with 71 assertions; full suite 174/174 with 596 assertions; PHP syntax PASS.
- Updated task queue: `IMP-S11-004` is `IN_PROGRESS` at 60%.
- Resume point: controlled Student status/history migration.

## 2026-09-04 — IMP-S11-004 closeout
- Closed S11-004 at 100% for preflight, bounded existing-Student master updates, effective status-history import, and append-only audit.
- Student creation, identifiers/guardian migration, and broader canonical import remain outside this task scope.
- Final verification: targeted import test 24/24 with 75 assertions; full suite 175/175 with 600 assertions; PHP syntax PASS.
- Updated task queue and task context to `IMP-S11-004` `DONE`; S12-001 is next and remains inactive until explicitly started.
- Database migration was not executed; pending migration deployment still requires explicit approval.

## 2026-09-04 — IMP-S12-001 atomic checkpoint: regression baseline
- Activated S12-001 and executed the full application regression plus targeted negative-RBAC suite.
- Full suite: 175/175 tests, 600 assertions; targeted authorization set: 20/20 tests, 48 assertions — all PASS.
- No application behavior changed in this baseline slice; remaining work is to compare the UAT catalogue with independently covered P0/P1/RBAC cases and add confirmed gaps.
- Updated task queue: `IMP-S12-001` is `IN_PROGRESS` at 25%.
- Resume point: close confirmed P0/P1/RBAC coverage gaps.

## 2026-09-04 — IMP-S12-001 atomic checkpoint: UAT coverage mapping
- Mapped the UAT catalogue to existing automated test areas and separated covered evidence from confirmed follow-up candidates.
- Documented gaps include dedicated CORE-003, CLS-006, CAL-002/SCH-CF-011, paper workflow, and MIG-005 assertions, plus non-automated operational gates.
- No application behavior changed in this mapping slice.
- Updated task queue: `IMP-S12-001` is `IN_PROGRESS` at 40%.
- Resume point: add confirmed P0/P1/RBAC coverage gaps.

## 2026-09-04 — IMP-S12-001 atomic checkpoint: CAL-002 regression
- Added regression coverage for `CAL-002`: `REVIEW_REQUIRED` calendar events enter the exception path and do not silently block session generation.
- Full suite after the test addition: 176/176 tests, 602 assertions — PASS; targeted calendar generator suite: 4/4 tests, 15 assertions — PASS.
- Updated UAT coverage map: `CAL-002` covered; `CORE-003`, `CLS-006`, and `SCH-CF-011` remain open.
- Updated task queue: `IMP-S12-001` is `IN_PROGRESS` at 50%.
- Resume point: close remaining confirmed P0/P1/RBAC gaps.

## 2026-09-04 — IMP-S12-001 atomic checkpoint: CORE-003 regression
- Added regression coverage proving an unknown NISN remains quarantined/unresolved, creates no placeholder Student, and leaves `canonical_entity_id` empty.
- Updated UAT coverage map: `CORE-003` and `CAL-002` covered; `CLS-006` and `SCH-CF-011` remain open.
- Updated task queue: `IMP-S12-001` is `IN_PROGRESS` at 60%.
- Resume point: close `CLS-006` and `SCH-CF-011` gaps.

## 2026-09-04 — IMP-S12-001 atomic checkpoint: CORE-003 regression verified
- Verified the new unknown-NISN/no-placeholder regression: targeted import suite 25/25 tests, 79 assertions — PASS.
- Full application suite after the regression addition: 177/177 tests, 606 assertions — PASS.
- `CORE-003` is covered; `CLS-006` and `SCH-CF-011` remain open implementation/test gaps.

## 2026-09-04 — IMP-S11-004 atomic checkpoint: Student status-history executor
- Added a transaction-scoped status-history importer for existing mapped Students, validating allowed statuses and effective intervals, preserving prior history, and recording append-only audit.
- Overlapping intervals and invalid date ranges remain blocked by the existing Shared Core model contract.
- Verification: targeted import test 24/24 with 75 assertions; full suite 175/175 with 600 assertions; PHP syntax PASS.
- Updated task queue: `IMP-S11-004` is `IN_PROGRESS` at 80%.
- Resume point: identifiers/guardian migration or S11-004 closeout review.

## 2026-09-04 — IMP-S11-003 atomic checkpoint: fixture field dictionary
- Added a fixture-only daily/monthly field dictionary proposal with grain, preservation rules, identity handling, and approval gates.
- Verified the monthly sample counters reconcile to 22 aggregate days per row; no production semantics or migration schema was inferred.
- Updated task queue: `IMP-S11-003` is `IN_PROGRESS` at 80%.
- Resume point: institutional approval of production-equivalent fields before legacy migration creation.

## 2026-09-04 — IMP-S12-001 atomic checkpoint: CLS-006 grade-level aggregation
- Added additive `grade_levels` dashboard output, grouped by canonical `grade_level_id` and preserving class-level detail.
- Added a regression proving two classes sharing one grade level aggregate into one grade-level row without parsing display names.
- Targeted dashboard suite: 7/7 tests, 15 assertions — PASS; full application suite: 178/178 tests, 610 assertions — PASS.
- No migration applied and no persistent business data changed.
- Updated task queue to 75%; `CLS-006` is covered and `SCH-CF-011` is the remaining confirmed gap.
- Resume point: implement the dedicated imported-schedule-overlap assertion and verified data-quality path for `SCH-CF-011`.

## 2026-09-04 — IMP-S12-001 atomic checkpoint: SCH-CF-011 imported overlap
- Added a dedicated regression for an `IMPORTED` schedule candidate overlapping an active class rule.
- Verified `ScheduleRuleConflictChecker` returns one structured `HIGH` `CLASS_CONFLICT` tied to the canonical class resource before acceptance.
- Targeted schedule suite: 4/4 tests, 17 assertions — PASS; full application suite: 179/179 tests, 614 assertions — PASS.
- No application behavior, migration, or persistent business data changed.
- Updated task queue to 85%; `SCH-CF-011` is covered and `MIG-005` remains the next confirmed gap.
- Resume point: add canonical semester-grade import provenance coverage for `MIG-005`.

## 2026-09-04 — IMP-S12-001 closeout: MIG-005 provenance regression
- Added a dedicated regression proving `IMPORTED` semester grades preserve canonical teaching-assignment provenance and reject mismatched provenance through the existing service contract.
- Targeted semester-grade suite: 7/7 tests, 16 assertions — PASS; full application suite: 180/180 tests, 618 assertions — PASS.
- All confirmed P0/P1/RBAC gaps in the coverage map are now covered: `CAL-002`, `CORE-003`, `CLS-006`, `SCH-CF-011`, and `MIG-005`.
- No migration applied and no persistent business data changed.
- `IMP-S12-001` closed at 100%; next task is `IMP-S12-002` concurrency/idempotency/atomicity hardening.

## 2026-09-04 — IMP-S12-002 atomic checkpoint: teacher participation idempotency
- Wrapped `TeacherParticipationRecorder::ensurePrimary` in a transaction with a session row lock before idempotent PRIMARY obligation creation.
- Regression confirms repeated calls keep one row and preserve an existing `attendance_status`.
- Targeted suite: 3/3 tests, 16 assertions — PASS; no migration or persistent business data changed.
- Updated task queue to 20%; resume with the next selected concurrency/idempotency/atomicity workflow.

## 2026-09-04 — IMP-S12-002 atomic checkpoint: participant snapshot idempotency
- Wrapped `SessionParticipantSnapshotter::snapshot` in a transaction with a session row lock before creating participant snapshots.
- Regression confirms repeated snapshots keep one row and preserve an existing `participant_status`.
- Targeted suite: 3/3 tests, 12 assertions — PASS; no migration or persistent business data changed.
- Updated task queue to 35%; resume with the next selected concurrency/idempotency/atomicity workflow.

## 2026-09-04 — IMP-S12-002 atomic checkpoint: session generation idempotency
- Wrapped `ClassSessionGenerator::generate` in a transaction with a schedule-rule row lock before idempotent session creation.
- Regression confirms repeated generation keeps the same sessions and preserves an existing `COMPLETED` status.
- Targeted suite: 4/4 tests, 16 assertions — PASS; no migration or persistent business data changed.
- Updated task queue to 50%; resume with the next selected concurrency/idempotency/atomicity workflow.

## 2026-09-04 — IMP-S12-002 atomic checkpoint: attendance finalization retry
- Added a regression proving repeated Wali Kelas attendance finalization is idempotent: no duplicate audit and no additional session version increment.
- Confirmed existing transaction/row-lock/rollback behavior remains intact.
- Targeted suite: 6/6 tests, 16 assertions — PASS; no migration or persistent business data changed.
- Updated task queue to 65%; resume with the next selected concurrency/idempotency/atomicity workflow.

## 2026-09-04 — IMP-S12-002 atomic checkpoint: import outcome retry
- Added retry regression for `ImportIntakeService::accountRowOutcome`.
- Verified a completed row cannot be accounted twice, and repeated calls do not duplicate `ImportRowError` or batch counters.
- Targeted import suite: 25/25 tests, 82 assertions — PASS; no migration or persistent business data changed.
- Updated task queue to 80%; resume with final full regression and closeout review.

## 2026-09-04 — IMP-S12-002 closeout: full regression
- Ran the full application suite after all concurrency/idempotency/atomicity slices: 181/181 tests, 627 assertions — PASS.
- Closed `IMP-S12-002` at 100%; teacher participation, participant snapshot, session generation, attendance finalization, and import outcome retry paths are covered.
- No migration applied and no persistent business data changed.
- Next task: `IMP-S12-003` performance baseline.

## 2026-09-04 — IMP-S12-003 atomic checkpoint: initial local baseline
- Ran `php artisan test tests/Feature/Academic` as the first reproducible performance baseline.
- Result: 127/127 tests, 404 assertions — PASS; observed duration 965 ms.
- Recorded methodology and limitations in `codex/PERFORMANCE_BASELINE.md`; no optimization, migration, or production capacity claim was made.
- Updated task queue to 40%; resume with representative route/query timing or an agreed load harness.

## 2026-09-04 — IMP-S12-003 atomic checkpoint: authenticated dashboard route
- Added an authorized `/academic/dashboard` route regression using the Wali Kelas fixture.
- Measured route-suite baseline: 8/8 tests, 18 assertions — PASS; wall time 0.81 s, PHPUnit duration 271 ms.
- Recorded the fixture and limitation in `codex/PERFORMANCE_BASELINE.md`; no optimization or production capacity claim was made.
- Updated task queue to 55%; resume with representative query count or an agreed load harness.

## 2026-09-04 — IMP-S12-003 atomic checkpoint: dashboard query guardrail
- Added a database query-count guardrail around the two-class Academic dashboard service fixture.
- Result: 9/9 tests, 20 assertions — PASS; query count remained at or below 20.
- Recorded the limitation that this is a regression guardrail, not a production capacity target.
- Updated task queue to 70%; resume with an agreed load harness or closeout regression.

## 2026-09-04 — IMP-S12-003 atomic checkpoint: safe concurrent-request harness
- Added `scripts/benchmark_academic_dashboard.php`, a bounded CLI harness requiring an explicit local URL and reporting status/error/latency metrics.
- Validation: PHP syntax and help output — PASS.
- Live execution was intentionally deferred because no local server/authenticated test session was available; no production target was used.
- Updated task queue to 85%; resume with local server/session execution or closeout regression.

## 2026-09-04 — IMP-S12-003 closeout: performance evidence
- Ran the full application suite after performance baseline additions: 183/183 tests, 632 assertions — PASS.
- Closed `IMP-S12-003` at 100%; local Academic suite, authenticated dashboard route, query guardrail, and safe load harness are recorded.
- Live concurrent load remains an operational follow-up because no local authenticated server session was available.
- No migration applied and no persistent business data changed.
- Next task: `IMP-S12-004` backup + restore test.

## 2026-09-04 — IMP-S12-004 atomic checkpoint: backup archive verification
- Created `/private/tmp/imtaq_backup_20260904.dump` with `pg_dump` from the local `imtaq` database.
- Verified the custom archive with `pg_restore --list`: 280 TOC entries, PostgreSQL 18.6.
- Restore to a temporary database is blocked because `imtaq_app` lacks `CREATEDB` and local role `postgres` does not exist.
- Main database was not modified; no migration or persistent application data change occurred.
- Updated task queue to 40%; resume once a database-admin-capable temporary restore target is available.

## 2026-09-04 — IMP-S12-004 closeout: backup and restore verification
- Created and archive-verified `/private/tmp/imtaq_backup_20260904.dump` with 280 TOC entries.
- Created temporary database `imtaq_restore_check_20260904`, restored the dump successfully, and verified 40 public tables plus core counts.
- Dropped the temporary database; read-only existence check returned false. Main `imtaq` database was not modified.
- Closed `IMP-S12-004` at 100%; next task is `IMP-S12-005` business UAT evidence.

## 2026-09-04 — IMP-S12-005 atomic checkpoint: defect and sign-off records
- Extended the UAT evidence pack with a structured defect register, severity guidance, and Wali Kelas/Waka Akademik/business-owner sign-off record.
- Kept all UAT decisions `PENDING`; no business acceptance was inferred.
- Updated task queue to 45%; resume with actual business review or recorded UAT outcomes.

## 2026-09-04 — IMP-S12-005 atomic checkpoint: UAT accepted
- Recorded the owner-reported UAT result: Wali Kelas, Waka Akademik, and Business Owner all accepted the Academic workflow; no defects were reported.
- Updated the UAT pack checklist and sign-off record to `ACCEPTED`; names/signatures remain blank because they were not supplied.
- Updated task queue to 75%; resume with formal identity/signature details or controlled pilot preparation.

## 2026-09-04 — IMP-S12-005 closeout: formal UAT identities
- Recorded formal UAT identities: Azhar (Wali Kelas), Wawan SN (Waka Akademik), and Abu Ubaidah (Business Owner), all `ACCEPTED` on 2026-09-04.
- No defects were reported; UAT evidence pack is now formally identified and complete.
- Closed `IMP-S12-005` at 100%; next task is `IMP-S12-006` controlled 1–2 class pilot.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: pilot preparation
- Prepared `codex/PILOT_PLAN_S12-006.md` with scope placeholders, preconditions, execution checklist, success criteria, defect log, and closeout fields.
- Did not select classes, assign dates, or start pilot data entry on behalf of the business owner.
- Updated task queue to 25%; resume with pilot class/period selection or plan review.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: pilot scope selected
- Recorded pilot classes 3A and 3B for 4–12 September 2026.
- Recorded Azhar as Wali Kelas and Wawan SN as pilot coordinator.
- Updated the pilot plan and task metadata to 50%; no pilot data entry or live execution performed.
- Resume point: pre-pilot briefing, roster/schedule checks, and backup checkpoint.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: pre-pilot prerequisite check
- Read-only database check found no canonical classes matching 3A/3B, no active homeroom assignments, and no scheduled sessions.
- Pilot execution was not started and no data was created by assumption.
- Recorded `BLOCKED_PREREQUISITE` in the pilot plan; updated task queue to 55%.
- Resume point: provision/confirm class master, Wali Kelas assignments, roster/enrollment, and schedule, then rerun pre-pilot checks.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: local pilot sample provision
- Added an idempotent `AcademicPilotSampleSeeder` for the selected 3A/3B pilot scope, including sample staff, Azhar homeroom assignments, roster/enrollment, approved schedules, and planned sessions.
- Added a focused idempotency test; it passed with 1 test and 5 assertions.
- Ran the seeder successfully on the local database; read-only verification returned 2 classes, 4 staff, 10 enrollments, 2 schedule rules, and 4 sessions.
- Did not run pending migrations; `user_staff_links` was absent, so account-to-staff linking is conditional and remains a schema prerequisite for authenticated pilot execution.
- Updated task metadata to 70%; resume with readiness/briefing checks, not attendance execution.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: pilot readiness verification
- Verified read-only: 2 active classes, 2 active homeroom assignments, 10 sample enrollments, 2 approved schedule rules, and 4 planned sessions.
- Verified sample role assignments: Azhar=`WALI_KELAS`; Wawan SN=`WAKA_AKADEMIK`.
- Found two blockers: `user_staff_links` table is missing, and participant snapshots are 0 for the 4 sessions.
- Did not run migration, snapshot creation, or attendance entry; task remains 70% and ready for an explicit blocker-resolution checkpoint.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: participant snapshot preparation
- Created/found 20 expected participant snapshots across the 4 pilot sessions (10 per class); rerun-safe service path used.
- Verified read-only: 3A has 2 sessions/10 participants and 3B has 2 sessions/10 participants.
- Confirmed attendance migration and staff-link migration are both pending; no migration or attendance entry was performed.
- Safe resume point: migration-gate decision and targeted application of only approved pending schema, then staff-linked access verification.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: access schema and pilot readiness
- Applied only the explicitly approved `student_attendance` and `user_staff_links` migrations; all later migrations remain pending.
- Reran the idempotent sample seeder; verified Azhar and Wawan SN staff links.
- Verified read-only: 20 participant snapshots remain present and attendance rows remain 0.
- Targeted attendance/context tests passed: 7 tests, 27 assertions.
- Updated task metadata to 80%; resume with backup checkpoint and workflow briefing before any attendance entry.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: pre-entry backup and briefing preparation
- Created `/private/tmp/imtaq_pilot_preentry_20260904.dump`; `pg_restore --list` verified 309 TOC entries and SHA-256 was recorded.
- Prepared `codex/PILOT_BRIEFING_S12-006.md` covering Wali Kelas, Waka Akademik, Super Admin, per-session attendance, notes, and correction boundaries.
- Did not infer briefing delivery or enter attendance; task updated to 85%.
- Safe resume point: confirm briefing delivery, then begin controlled sample attendance entry.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: controlled sample attendance execution
- Created primary teacher participations for 4 sample sessions and recorded teacher PRESENT by Azhar.
- Entered and finalized 40 student attendance rows: per class 6 PRESENT, 2 ABSENT, and 2 IZIN with notes.
- Verified all 4 sessions are `COMPLETED` and all attendance rows are `VALIDATED`.
- Targeted pilot tests passed: 11 tests, 29 assertions; no business closeout inferred.
- Updated task metadata to 95%; safe resume point is Waka Akademik review and pilot closeout.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: Waka technical review
- Reviewed the sample pilot through Wawan SN's `WAKA_AKADEMIK` dashboard access.
- Verified exactly 2 classes; each has 2/2 completed sessions and 10/10 resolved attendance opportunities.
- Targeted dashboard tests passed: 9 tests, 20 assertions; no technical defect found.
- Did not infer formal business sign-off; updated task metadata to 98%.
- Safe resume point: formal Waka Akademik and Business Owner closeout confirmation.

## 2026-09-04 — IMP-S12-006 atomic checkpoint: closeout pack
- Prepared `codex/PILOT_CLOSEOUT_S12-006.md` with pilot evidence and explicit confirmation fields for Azhar, Wawan SN, and Abu Ubaidah.
- Did not infer formal closeout from technical review or menu selection.
- Updated task metadata to 99%; safe resume point is explicit Waka Akademik/Business Owner closeout confirmation.

## 2026-09-04 — IMP-S12-006 closeout
- Recorded explicit acceptance from Wawan SN (Waka Akademik) and Abu Ubaidah (Business Owner) for the sample pilot result.
- Recorded Azhar as the pilot inputter; no further attendance changes were made.
- Closed `IMP-S12-006` at 100%; next queue task is `IMP-S12-007` production cutover readiness review.

## 2026-09-04 — IMP-ADM-001 atomic checkpoint: admin class list/create
- Added backend-authorized Admin routes for Academic class list/create.
- Added server-side validation against canonical academic year, organization unit, and grade level masters.
- Added focused admin feature tests: 2 tests, 6 assertions — PASS.
- Did not change pilot data or apply a migration; next safe point is teacher master list/create.

## 2026-09-04 — IMP-ADM-001 atomic checkpoint: admin staff list/create
- Added backend-authorized Admin routes for canonical Shared Core staff list/create.
- Added active-date validation and Indonesian list/create views.
- Added focused staff feature tests; class regression included: 4 tests, 12 assertions — PASS.
- Did not add staff sample data or change pilot data; next safe point is schedule master list/create.

## 2026-09-04 — IMP-ADM-001 atomic checkpoint: admin schedule list/create
- Added backend-authorized Admin routes for schedule rule list/create.
- Added validation for teaching assignment, weekday, time order, recurrence, and bounded effective period.
- Schedule creation generates bounded class sessions through the existing `ClassSessionGenerator`.
- Combined admin tests passed: 6 tests, 19 assertions; no migration or pilot-data mutation.
- Completed the requested class/staff/schedule addition slice; edit/delete remains separate.

## 2026-09-04 — IMP-ADM-002 atomic checkpoint: Academic Admin landing navigation
- Added an authorized Academic Admin landing page linking Kelas, Guru/Staff, and Jadwal.
- Verified 10 admin routes are registered and admin/non-admin dashboard access behavior.
- Admin regression passed: 8 tests, 24 assertions; no migration or pilot-data mutation.
- Completed the navigation checkpoint; next safe point is historical-safe edit flows or cutover readiness.

## 2026-09-04 — IMP-ADM-003 atomic checkpoint: historical-safe class edit
- Added Admin class edit/update routes and view.
- Restricted updates to display name and status; class code, section, academic year, unit, and grade identity remain immutable.
- Class admin tests passed: 3 tests, 11 assertions; no migration or pilot-data mutation.
- Completed class edit checkpoint; next safe point is staff edit or schedule edit.

## 2026-09-04 — IMP-ADM-004 atomic checkpoint: historical-safe staff edit
- Added Admin staff edit/update routes and view.
- Restricted updates to full name, record status, and active interval; staff code remains immutable.
- Admin regression passed: 10 tests, 34 assertions; no migration, assignment mutation, or pilot-data mutation.
- Completed staff edit checkpoint; next safe point is schedule edit.

## 2026-09-04 — IMP-ADM-005 atomic checkpoint: historical-safe schedule edit
- Added Admin schedule edit/update routes and view.
- Allowed edits only for rules without generated sessions; existing-session rules return a validation error directing admins to create a new rule.
- Admin regression passed: 12 tests, 39 assertions; no migration or pilot-data mutation.
- Completed schedule edit checkpoint; next safe point is browser smoke test or production cutover readiness.

## 2026-09-04 — IMP-AUTH-001 atomic checkpoint: local authentication and browser smoke
- Added minimal local login/logout for the protected Academic Admin routes; Google/Gmail SSO remains deferred.
- Added idempotent `ADMIN_AKADEMIK` pilot account `admin.pilot@example.test` and seeded it successfully.
- Local authentication feature test passed: 4 tests, 16 assertions; Pint passed.
- Browser smoke reached Dashboard Admin Akademik and rendered Kelas, Guru/Staff, and Jadwal with pilot data.
- No migration or historical attendance-data mutation; next safe point is production cutover readiness review.

## 2026-09-04 — IMP-S12-007 atomic checkpoint: production readiness review
- Foundation verification passed: 200 tests, 692 assertions.
- Read-only PostgreSQL migration status confirmed pilot baseline through migration `000019`; later migrations remain pending.
- Confirmed local Admin browser smoke and recorded the release readiness evidence.
- Decision: `NOT_READY_FOR_PRODUCTION` because no Git release revision, staging target, backup/restore policy, production configuration, monitoring, or rollback evidence is available.
- No deployment or migration command was run.

## 2026-09-04 — IMP-S12-008 atomic checkpoint: staging preparation
- Confirmed locked dependencies and non-secret environment template are available.
- Recorded local foundation, migration, browser smoke, and staging smoke evidence.
- Recorded that no exact Git revision, staging target, backup/restore policy, secret provisioning, monitoring, rollback rehearsal, or pending-migration review is available yet.
- Result: `STAGING_PREPARATION_INCOMPLETE`; no deployment or migration command was run.

## 2026-09-04 — IMP-S12-009 checkpoint: release candidate blocker
- Confirmed no Git worktree, remote, or exact revision is available in the project root.
- Confirmed no staging provider/host/database target is configured.
- Detected local `application/web/.env`; no release snapshot or new Git history was created.
- Result: `BLOCKED_PENDING_REPOSITORY_AND_ENVIRONMENT`; local pilot/UAT remains safe to continue.

## 2026-09-04 — IMP-UI-001 atomic checkpoint: Academic Admin visual shell
- Added shared responsive styling and consistent navigation for Dashboard, Kelas, Guru/Staff, and Jadwal.
- Admin regression passed: 12 tests, 39 assertions; Blade cache compile/clear passed.
- Browser preview visibly confirmed the updated Dashboard Admin Akademik shell.
- Presentation-only change; no route, RBAC, business-rule, migration, or persistent-data changes.

## 2026-09-04 — IMP-UI-003 atomic checkpoint: Academic Admin KPI dashboard
- Added read-only KPI counts for active classes, active staff, approved schedules, and present attendance.
- Added current-month recent session activity list.
- Admin/auth regression passed: 6 tests, 21 assertions; Blade cache compile/clear passed.
- Browser preview verified pilot values and four current-month sessions.
- No new tables, route changes, business-rule changes, or data writes.

## 2026-09-04 — IMP-UI-004 atomic checkpoint: attendance UI polish
- Added attendance summary cards, clearer instructions, status emphasis, notes guidance, and responsive entry table styling.
- Unified Exception Attendance styling and finding badges with the Admin visual system.
- Attendance regression passed: 13 tests, 33 assertions; Blade cache compile/clear passed.
- Presentation-only change; attendance workflow, RBAC, validation, and data services are unchanged.

## 2026-09-04 — IMP-UI-005 atomic checkpoint: role navigation and logout
- Added authenticated role context and logout affordance to Admin pages.
- Added Academic navigation, role context, and logout to attendance views.
- Admin/auth/attendance regression passed: 10 tests, 34 assertions; Blade cache compile/clear passed.
- Browser preview verified role label and logout action.
- No authorization policy, route, workflow, migration, or persistent-data changes.

## 2026-09-04 — IMP-UI-006 atomic checkpoint: local visual review
- Reviewed Dashboard, Master Kelas, Login, and Attendance in the local browser.
- Confirmed hierarchy, controls, status display, responsive containment, and role/logout context are usable.
- No confirmed visual defect justified a code change in this checkpoint.
- Result recorded as `PASS_WITH_NO_CONFIRMED_DEFECTS`.

## 2026-09-04 — IMP-UI-007 atomic checkpoint: attendance summary chart
- Added read-only grouped attendance totals and proportional bars to the Admin Dashboard.
- Admin/auth regression passed: 6 tests, 21 assertions; Pint and Blade compile passed.
- Browser preview verified pilot totals: Hadir 12, Tidak hadir 4, Izin 4, others 0.
- No new tables, route changes, workflow changes, or data writes.

## 2026-09-04 — IMP-UI-008 atomic checkpoint: mobile attendance guidance
- Added explicit horizontal-scroll guidance to attendance and exception tables for narrow screens.
- Attendance regression passed: 4 tests, 13 assertions; Blade cache compile/clear passed.
- No route, authorization, workflow, validation, migration, or persistent-data changes.

## 2026-09-04 — IMP-UI-009 atomic checkpoint: Dashboard spacing fix
- Fixed the confirmed visual defect where the Jadwal card was too close to the account/logout footer.
- Admin Dashboard test passed: 2 tests, 5 assertions; Blade cache compile/clear passed.
- Browser screenshot verified clear vertical separation.
- Presentation-only change; no route, RBAC, workflow, migration, or data changes.

## 2026-09-04 — IMP-UI-011 atomic checkpoint: attendance action polish
- Fixed the Finalisasi attendance button styling to match Simpan draft.
- Attendance regression passed: 4 tests, 13 assertions; Blade cache compile/clear passed.
- Browser screenshot verified both actions are consistent.
- No route, RBAC, workflow, validation, migration, or data changes.

## 2026-09-04 — IMP-UI-012 atomic checkpoint: Exception Attendance empty state
- Added a clear-state indicator and explanatory copy when no attendance exceptions exist.
- Exception Attendance test passed: 2 tests, 5 assertions; Blade cache compile/clear passed.
- Admin browser preview verified the improved empty state.
- Observed Waka Akademik 403 authorization mismatch; deliberately left RBAC unchanged for separate policy approval.

## 2026-09-04 — IMP-RBAC-001 atomic checkpoint: Waka Exception Attendance access
- Allowed `WAKA_AKADEMIK` alongside `ADMIN_AKADEMIK` in the backend exception monitor.
- Kept unrelated `GURU` role denied.
- Exception Attendance regression passed: 3 tests, 8 assertions; Pint passed.
- Browser smoke verified Waka Akademik reaches the read-only page.
- No migration, route, workflow, or persistent-data changes.

## 2026-09-04 — IMP-UI-013 atomic checkpoint: Waka Academic Dashboard review
- Aligned Academic Dashboard with the shared visual system.
- Added Academic Dashboard/Exception Attendance navigation, role context, and logout.
- Academic dashboard regression passed: 9 tests, 20 assertions; Blade cache compile/clear passed.
- Browser preview verified as Waka Akademik.
- No authorization, workflow, migration, or persistent-data changes.

## 2026-09-04 — IMP-UI-010 atomic checkpoint: KPI/attendance spacing fix
- Added clear vertical separation between the attendance KPI block and attendance composition/activity blocks.
- Admin Dashboard test passed: 2 tests, 5 assertions; Blade cache compile/clear passed.
- Browser screenshot verified the blocks no longer touch.
- Presentation-only change; no route, RBAC, business-rule, migration, or data changes.

## 2026-09-04 — IMP-UI-002 atomic checkpoint: login and form polish
- Unified Login Sistem IMTAQ and six Admin create/edit forms with shared styling.
- Improved focus, readonly, error, button, heading, spacing, and responsive states.
- Admin/auth regression passed: 16 tests, 55 assertions; Blade cache compile/clear passed.
- Browser preview verified login and Tambah Kelas rendering.
- Presentation-only change; no route, RBAC, business-rule, migration, or persistent-data changes.
# 2026-09-04 — IMP-UI-014 atomic checkpoint: standardisasi istilah UI pesantren
- Mengganti istilah teknis/Inggris yang terlihat pengguna menjadi istilah yang lebih familiar: Kehadiran, Ringkasan Akademik, Perlu Perhatian Kehadiran, Status pengisian, Penugasan mengajar, Data induk, dan Guru/Staf.
- Technical identifiers, route, RBAC, business rule, migration, dan data persisten tidak berubah.
- 16 test terarah dengan 41 assertion lulus; Blade cache compile/clear lulus; browser lokal memverifikasi halaman Perlu Perhatian Kehadiran.
- Sisa copy Admin Dashboard dicatat untuk `IMP-UI-015`.
# 2026-09-04 — IMP-UI-015 atomic checkpoint: standardisasi Admin Dashboard dan master data
- Menyeragamkan istilah pada Dashboard Admin, master kelas, master guru/staf, dan master jadwal agar lebih mudah dipahami operator pesantren.
- Mengganti label seperti “Shared Core”, “Guru/Staff”, “ACTIVE/INACTIVE”, dan “APPROVED” pada tampilan; identifier teknis tetap dipertahankan.
- Tidak ada perubahan pada route, RBAC, business rule, migration, atau data persisten.
- 8 test dengan 27 assertion lulus; Blade cache compile/clear lulus.
- Sisa pemetaan status enum teknis dicatat untuk `IMP-UI-016`.
# 2026-09-04 — IMP-UI-016 atomic checkpoint: pemetaan status teknis pada UI
- Memetakan status sesi, kehadiran, kelas, staf, dan jadwal ke label Indonesia yang lebih mudah dipahami; nilai enum internal tetap sama.
- Tidak ada perubahan pada route, RBAC, business rule, migration, atau data persisten.
- 13 test dengan 43 assertion lulus; Blade cache compile/clear lulus.
- Resume point: review tampilan mobile halaman master data pada `IMP-UI-017`.
# 2026-09-04 — IMP-UI-017 atomic checkpoint: penyelarasan istilah alur akademik
- Mengganti “locked” menjadi “terkunci”, “Simpan draft” menjadi “Simpan sementara”, dan menjelaskan isian sementara dengan bahasa yang lebih mudah dipahami.
- Tidak ada perubahan pada route, RBAC, business rule, migration, enum internal, atau data persisten.
- 23 test dengan 68 assertion lulus; Pint lulus; Blade cache compile/clear lulus.
- Resume point: review tampilan mobile halaman master data pada `IMP-UI-018`.
# 2026-09-04 — IMP-UI-018 atomic checkpoint: petunjuk tabel mobile master data
- Menambahkan petunjuk “Pada layar kecil, geser tabel ke kiri/kanan untuk melihat semua kolom” pada master kelas, guru/staf, dan jadwal.
- Browser lokal memverifikasi petunjuk serta status “Disetujui” pada halaman master jadwal.
- 8 test dengan 27 assertion lulus; Blade cache compile/clear lulus.
- Tidak ada perubahan pada route, RBAC, business rule, migration, atau data persisten.
# 2026-09-04 — IMP-UI-019 atomic checkpoint: verifikasi responsif master data
- Memverifikasi halaman master kelas, guru/staf, dan jadwal pada browser lokal dengan lebar tampilan sempit.
- Navigasi, tabel, petunjuk geser horizontal, dan label status Indonesia terbaca; tidak ada masalah baru yang memerlukan perubahan kode.
- Resume point: lanjutkan checkpoint UI berikutnya pada `IMP-UI-020`.
# 2026-09-04 — IMP-UI-020 atomic checkpoint: istilah form master kelas
- Mengganti “Master data” menjadi “Data induk” dan “Section” menjadi “Rombel/Bagian” pada alur master kelas.
- Technical field name `section_code` tetap dipertahankan agar tidak mengubah kontrak aplikasi.
- 6 test dengan 22 assertion lulus; Blade cache compile/clear lulus.
- Resume point: lanjutkan checkpoint UI berikutnya pada `IMP-UI-021`.
# 2026-09-04 — IMP-UI-021 atomic checkpoint: istilah periode tugas Guru/Staf
- Mengganti “Mulai aktif” dan “Selesai aktif” menjadi “Mulai bertugas” dan “Selesai bertugas” pada form tambah/edit Guru/Staf.
- Tidak ada perubahan pada nama field, route, RBAC, business rule, migration, atau data persisten.
- 3 test dengan 11 assertion lulus; Blade cache compile/clear lulus.
- Resume point: lanjutkan checkpoint UI berikutnya pada `IMP-UI-022`.
# 2026-09-04 — IMP-UI-022 atomic checkpoint: istilah login dan kehadiran
- Mengganti “Login Sistem IMTAQ” menjadi “Masuk ke Sistem IMTAQ”, “Email” menjadi “Alamat email”, “Password” menjadi “Kata sandi”, dan “Inputter” menjadi “Diisi oleh”.
- Tidak ada perubahan pada autentikasi, route, RBAC, business rule, migration, atau data persisten.
- 9 test dengan 32 assertion lulus; Pint lulus; Blade cache compile/clear lulus.
- Resume point: lanjutkan checkpoint UI berikutnya pada `IMP-UI-023`.
# 2026-09-04 — IMP-UI-023 atomic checkpoint: audit UAT dan laporan
- Mengaudit route/view frontend aktif dan menemukan belum ada halaman UAT atau laporan terpisah; yang aktif adalah Ringkasan Akademik dan ekspor rekap CSV.
- Istilah pada alur aktif sudah berbahasa Indonesia; tidak ada perubahan kode.
- 9 test dengan 20 assertion lulus; Blade cache compile/clear lulus.
- Resume point: lanjutkan checkpoint UI berikutnya pada `IMP-UI-024`.
# 2026-09-04 — IMP-UI-024 atomic checkpoint: checklist UAT pilot
- Menambahkan `codex/UAT_CHECKLIST_PILOT_S12-006.md` untuk kelas 3A/3B, periode 4–12 September 2026.
- Checklist mencakup persiapan, pengisian Wali Kelas, pemeriksaan Waka Akademik, keamanan/data, log masalah, dan tanda tangan keputusan.
- Dokumen tidak memulai UAT dan tidak mengubah aplikasi, database, atau data pilot.
- Resume point: menunggu pilihan owner untuk checkpoint `IMP-UI-025`.
# 2026-09-04 — IMP-UI-025 atomic checkpoint: review checklist UAT
- Meninjau checklist UAT pilot terhadap ruang lingkup 3A/3B, pembagian peran, skenario kehadiran, keamanan data, log masalah, dan tanda tangan.
- Semua bagian dinyatakan lengkap; checklist diberi status `SIAP DIGUNAKAN UNTUK UAT`.
- Tidak menganggap UAT sudah dijalankan atau hasil bisnis sudah disetujui.
- Resume point: actual UAT execution remains owner-controlled; next task `IMP-UI-026`.
# 2026-09-04 — IMP-UAT-001 atomic checkpoint: preflight dan akses Wali Kelas
- Akun Azhar berhasil masuk sebagai `WALI_KELAS` pada browser lokal.
- Ringkasan Akademik menampilkan kelas 3A dan 3B; PRE-01 sampai PRE-03 pada checklist UAT dicatat `LULUS`.
- Belum melakukan pengisian atau perubahan data kehadiran pada segmen ini.
- Resume point: lanjutkan pengisian kehadiran UAT pada `IMP-UAT-002`.
# 2026-09-04 — IMP-UAT-002 atomic checkpoint: verifikasi alur kehadiran Wali Kelas
- Melalui akun Azhar, membuka sesi sample 3A dan memverifikasi status Hadir, Tidak hadir, Izin per sesi, catatan, Selesai, dan Sudah diperiksa.
- ATT-01 sampai ATT-05, ATT-07, dan ATT-08 dicatat `LULUS` pada checklist UAT.
- Tidak menimpa data sample yang sudah selesai; ATT-06, ATT-09, ATT-10, dan review Waka belum dijalankan.
- Resume point: lanjutkan UAT negative-access atau review Waka pada `IMP-UAT-003`.
# 2026-09-04 — IMP-UAT-003 atomic checkpoint: pengisian cepat semua hadir
- Menambahkan tombol “Tandai semua hadir” dengan konfirmasi pada halaman Kehadiran Siswa.
- Wali Kelas tetap dapat mengubah pengecualian sebelum menyimpan; tidak ada perubahan backend atau data saat tombol ditekan.
- 2 test dengan 9 assertion lulus; Blade cache compile/clear lulus; browser lokal memverifikasi tombol dan instruksinya.
- Resume point: lanjutkan negative-access atau review Waka pada `IMP-UAT-004`.
# 2026-09-05 — IMP-REPORT-EXPORT-003 atomic checkpoint: tahun ajaran pada PDF
- Menambahkan `Tahun Ajaran 2026/2027` pada header dan footer PDF; CSV metadata juga memuat tahun ajaran.
- PDF valid satu halaman A4 landscape; PHP lint dan AdminDashboardTest 2/5 lulus.
- Resume point: `IMP-MIG-011` — pilih kebutuhan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-REPORT-EXPORT-002 atomic checkpoint: PDF profesional
- Memoles PDF dengan header hijau, tabel terstruktur, baris selang-seling, total berwarna, dan catatan kaki.
- PDF valid satu halaman A4; PHP lint, test AdminDashboard, Blade cache, dan route check lulus.
- Raster Poppler tidak tersedia karena Fontconfig lokal gagal memuat konfigurasi.
- Resume point: `IMP-MIG-011` — pilih kebutuhan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-REPORT-EXPORT-001 atomic checkpoint: ekspor CSV dan PDF
- Menambahkan tombol `Unduh CSV` dan `Unduh PDF rapi` pada laporan bulanan Juli.
- CSV berisi lima kelas dan total; PDF satu halaman A4 dengan tabel ringkas dan catatan non-eligible.
- PHP lint, route smoke check, AdminDashboardTest 2/5, Blade cache, dan browser UI check lulus. PDF `pdfinfo` valid; raster Poppler terkendala Fontconfig lokal.
- Resume point: `IMP-MIG-011` — pilih kebutuhan laporan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-MIG-012 atomic checkpoint: staging dan rekonsiliasi paket 84 santri
- Mengekstrak paket v2 ke direktori sementara dan membaca instruksi import sebelum data diproses.
- Memuat 84 roster historis dan 84 snapshot kehadiran Juli ke tabel staging PostgreSQL yang didefinisikan paket; seluruh `student_id` tetap NULL.
- Rekonsiliasi lulus: kelas 1=20, 2A=19, 2B=10, 3A=15, 3B=20; hadir 1126, izin 39, sakit 29, absen 4, eligible 1198, non-eligible 142; tidak ada baris yatim atau kesalahan formula/rate.
- SHA-256 keenam file cocok dengan manifest. Master canonical saat ini berisi 10 sample student dan 0 exact-name match terhadap roster historis.
- Tidak ada production migration, session fact, atau per-student attendance fact dibuat.
- Resume point: identity mapping review untuk 84 santri; production migration tetap diblokir.
# 2026-09-05 — IMP-MIG-013 atomic checkpoint: review mapping 84 santri
- Membandingkan roster staging dengan master canonical menggunakan exact normalized name sebagai sinyal kandidat saja.
- Hasil: 0/84 exact match; kelas 1=20, 2A=19, 2B=10, 3A=15, 3B=20; seluruh 84 tetap UNMAPPED/REVIEW_REQUIRED.
- Master canonical saat ini berisi 10 baris sample; tidak ada nama, `JUL26-xxx`, atau urutan yang dipakai sebagai Student_ID permanen.
- Tidak ada canonical student, enrollment, sesi, atau fakta kehadiran production yang dibuat/diubah.
- Resume point: masukkan/verifikasi roster canonical dengan permanent Student_ID dan kunci identitas terpercaya sebelum mapping 84/84.
# 2026-09-05 — IMP-ACCESS-001 atomic checkpoint: akses laporan Juli untuk Wali Kelas
- Menambahkan jalur baca `/academic/monthly-reports/july-2026` dan tautannya dari Ringkasan Akademik.
- Wali Kelas hanya melihat laporan kelas yang memiliki assignment aktif pada periode Juli; pada verifikasi akun Azhar tampil Kelas 3A saja.
- Publikasi dan ekspor Admin tetap dibatasi; tidak ada perubahan fakta kehadiran, mapping, atau lifecycle import.
- Test terarah lulus: 16 test dengan 43 assertion; route listing dan Blade cache lulus; browser smoke berhasil.
- Resume point: `IMP-MIG-011` — pilih kebutuhan laporan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-UI-021 atomic checkpoint: jarak footer dashboard Admin
- Menambah ruang vertikal sebelum footer “Masuk sebagai” agar tidak menempel dengan kartu navigasi terakhir.
- Data dashboard, laporan Juli, logout, dan hak akses tidak berubah.
- Resume point: `IMP-MIG-011` — pilih kebutuhan laporan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-UI-020 atomic checkpoint: jarak blok dashboard Admin
- Menambah ruang vertikal setelah grid metrik agar blok Komposisi Kehadiran dan Aktivitas Terbaru tidak menempel.
- Dashboard, angka, laporan Juli, navigasi, dan hak akses tidak berubah.
- Resume point: `IMP-MIG-011` — pilih kebutuhan laporan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-UI-019 atomic checkpoint: jarak form login
- Menambahkan jarak vertikal antara pilihan “Ingat saya” dan tombol “Masuk” pada halaman login.
- Alur login, endpoint, validasi, session, dan RBAC tidak berubah.
- Resume point: `IMP-MIG-011` — pilih kebutuhan laporan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-MIG-010 atomic checkpoint: bersihkan nama penyetuju
- Suffix teknis `(Pilot)` dihapus dari tampilan nama penyetuju; data kanonik dan audit tetap utuh.
- Verifikasi sebagai Waka menampilkan `Wawan SN`, status `Sudah diterbitkan`, dan waktu publikasi.
- Test AdminDashboard lulus: 2 test dengan 5 assertion; Blade cache lulus.
- Resume point: `IMP-MIG-011` — pilih laporan lanjutan atau tutup rangkaian import Juli.
# 2026-09-05 — IMP-MIG-009 atomic checkpoint: metadata penyetuju laporan
- Menambahkan nama penyetuju dan waktu publikasi pada lima baris laporan Juli.
- Halaman laporan menampilkan penyetuju Waka Akademik dan waktu persetujuan; dashboard tetap menampilkan `Sudah diterbitkan`.
- Test AdminDashboard lulus: 2 test dengan 5 assertion; migrasi dan Blade cache lulus.
- Resume point: `IMP-MIG-010` — review halaman laporan sebagai Waka jika diperlukan.
# 2026-09-05 — IMP-MIG-008 atomic checkpoint: badge status dashboard
- Badge pada kartu laporan bulanan Admin sekarang dihitung dari status lima baris Juli; setelah publikasi tampil `Sudah diterbitkan`.
- Test AdminDashboard lulus: 2 test dengan 5 assertion; Blade cache dan browser smoke check lulus.
- Resume point: `IMP-MIG-009` — review visual akhir atau lanjut ke riwayat laporan.
# 2026-09-05 — IMP-MIG-007 atomic checkpoint: publikasi laporan Juli
- Waka Akademik `wawan.sn.pilot@example.test` menyetujui rekap Juli melalui alur laporan.
- Lima baris berubah menjadi `PUBLISHED` dalam satu transaksi dan audit `MONTHLY_ATTENDANCE_REPORT_PUBLISHED` tercatat.
- Browser memverifikasi seluruh kelas menampilkan `Sudah diterbitkan`; tidak ada fakta sesi/per-santri dibuat.
- Resume point: `IMP-MIG-008` — menyamakan badge status pada dashboard Admin dengan status publikasi.
# 2026-09-05 — IMP-MIG-006 atomic checkpoint: persetujuan laporan Juli
- Menambahkan halaman pemeriksaan laporan dan tombol `Setujui & terbitkan` untuk Waka Akademik atau Super Admin.
- Publikasi mensyaratkan lima baris kelas, berjalan dalam transaksi, dan mencatat audit log; laporan belum dipublikasikan karena keputusan tetap owner-controlled.
- Test terarah lulus: 4 test dengan 14 assertion; PHP lint, route smoke check, dan Blade cache lulus.
- Resume point: owner memeriksa laporan Juli pada `/admin/academic/monthly-reports/july-2026`, lalu memilih publish atau lanjutkan penyempurnaan UI.
# 2026-09-05 — IMP-MIG-005 atomic checkpoint: rekap Juli di dashboard
- Menambahkan penyimpanan `MONTHLY_SUMMARY` berjejak checksum/import lineage dan mengimpor lima baris kelas Juli sebagai laporan `DRAFT` dalam batch `RECONCILED`.
- Dashboard menampilkan hadir 1.126, izin 39, sakit 29, absen 4, eligible 1.198, non-eligible 142, rate 93,99%.
- Data pilot tidak dihapus; sesi operasional sample dikeluarkan dari metrik aktivitas aktif agar laporan historis tidak tercampur.
- Test terarah lulus: 4 test dengan 14 assertion; browser lokal memverifikasi laporan lima kelas dan total Juli.
- Resume point: `IMP-MIG-006` — review dan alur persetujuan/publikasi laporan Juli oleh Waka Akademik.
# 2026-09-05 — IMP-UI-018 atomic checkpoint: label master data untuk UI
- Menambahkan formatter presentasi `UiLabel` dan directive Blade `@uiLabel` untuk menyembunyikan suffix teknis `(Pilot)`, `Sample Pilot`, `Sample`, dan `Pilot` dari label yang tampil.
- Menerapkan pada daftar/pilihan kelas, struktur tingkat dan Wali Kelas, jadwal, serta ringkasan akademik.
- Tidak mengubah database, identitas kanonik, mapping Class_Admin/Attendance_Group/MATIQ, eligibility, denominator, lifecycle, atau nilai mentah formulir edit.
- Test terarah lulus: 14 test dengan 31 assertion; Blade view cache berhasil; browser memverifikasi master kelas, struktur, dan jadwal tanpa label teknis.
- Resume point: `IMP-MIG-011` — pilih kebutuhan laporan lanjutan atau tutup rangkaian import Juli.
# 2026-09-04 — IMP-MIG-001 atomic checkpoint: kontrak import historis Juli 2026
- Menetapkan solusi rekap Juli sebagai `MONTHLY_SUMMARY` tingkat kelas menggunakan import infrastructure yang sudah ada.
- Mencatat checksum sumber, mapping Class_Admin/Attendance_Group/MATIQ, roster, rumus eligibility/denominator, dan angka rekonsiliasi.
- Menegaskan tidak ada pembuatan sesi, tanggal, data per-santri, teacher attendance, atau perubahan lifecycle/enum pada langkah ini.
- SHA-256 dan rekonsiliasi read-only seed lulus: roster 84; peluang 1.340; eligible 1.198; non-eligible 142; hadir 1.126; rate 93,99%.
- Resume point: implementasikan profiler/validator dry-run pada `IMP-MIG-002`; import nyata tetap menunggu mapping dan persetujuan.
# 2026-09-04 — IMP-MIG-002 atomic checkpoint: dry-run validator Juli 2026
- Menambahkan validator read-only dan command `imtaq:validate-july-2026` untuk memeriksa seed tanpa membuat batch, row, sesi, atau fakta kanonik.
- Validasi mencakup checksum, scope lima kelas, mapping, rumus eligible, denominator, peluang jadwal Juli, total keseluruhan, dan grup 2B-3B.
- Test terarah lulus: 2 test dengan 9 assertion; PHP lint lulus; CLI dry-run mengembalikan `valid=true` dan checksum cocok.
- Resume point: review hasil dry-run serta konfirmasi mapping kelas dan pemilik sumber pada `IMP-MIG-003`; import nyata belum diizinkan.
# 2026-09-04 — IMP-MIG-003 atomic checkpoint: review mapping dan pemilik sumber
- Meninjau handoff dan menetapkan mapping sumber lima kelas tanpa mengubah Class_Admin, Attendance_Group, MATIQ, eligibility, denominator, atau lifecycle.
- Owner/validator dari handoff adalah Waka Academic; identitas operasional yang telah diberikan adalah Wawan SN.
- Dry-run diterima pada level sumber, tetapi pemeriksaan primary key kelas kanonik dan assignment Wali Kelas belum dapat dilakukan karena koneksi PostgreSQL lokal ditolak lingkungan.
- Tidak ada staging batch atau import nyata dibuat.
- Resume point: pemeriksaan read-only database pada `IMP-MIG-004` setelah akses PostgreSQL tersedia.
# 2026-09-04 — IMP-MIG-004 atomic checkpoint: verifikasi identitas kelas kanonik
- Query SELECT read-only berhasil setelah akses PostgreSQL diberikan.
- Kelas aktif 3A dan 3B terverifikasi dengan Wali Kelas Azhar; Wawan SN terverifikasi sebagai Staff aktif dan validator Waka Academic.
- Kelas 1, 2A, dan 2B belum ada di master kanonik; tidak dilakukan auto-create atau import parsial.
- Import seluruh dataset Juli tetap diblokir sampai tiga master kelas dan assignment-nya tersedia serta diverifikasi.
- Resume point: tambahkan master kelas yang hilang melalui alur Admin pada `IMP-ADM-006`, lalu ulangi verifikasi read-only.
# 2026-09-04 — Input target wali kelas Juli 2026 diterima
- Mencatat target assignment: 1–Ust. Alwan, 2A–Ust. Afwa, 3A–Ust Azhar, 2B/3B–Ust. Maulana; validator Waka Akademik–Ust. Wawan.
- Tidak menulis data master karena dashboard saat ini hanya menyediakan Tingkat 3 dan belum menyediakan alur assignment Wali Kelas.
- Resume point tetap `IMP-ADM-006`: siapkan master tingkat dan alur assignment Admin sebelum pengisian data target.
# 2026-09-04 — IMP-ADM-006 atomic checkpoint: dashboard Tingkat dan Wali Kelas
- Menambahkan halaman Admin “Tingkat & Wali Kelas” untuk membuat GradeLevel dan assignment Wali Kelas memakai tabel yang sudah ada.
- Endpoint dibatasi untuk SUPER_ADMIN/ADMIN_AKADEMIK dan assignment menolak periode yang tumpang tindih melalui invariant model.
- Test terarah lulus: 2 test dengan 6 assertion; PHP lint dan route smoke check lulus.
- Belum memasukkan Staff, GradeLevel, Class, atau assignment target; resume point `IMP-ADM-007` adalah pengisian data melalui dashboard.
# 2026-09-04 — IMP-ADM-007 atomic checkpoint: pengisian master data Juli
- Melalui dashboard Admin, menambahkan Staff Ust. Alwan, Ust. Afwa, dan Ust. Maulana.
- Menambahkan master Tingkat 1 dan 2 serta kelas 1, 2A, dan 2B.
- Menetapkan Wali Kelas: 1–Alwan, 2A–Afwa, 2B–Maulana; memperbarui 3B dari Azhar ke Maulana; 3A tetap Azhar.
- Verifikasi read-only database cocok untuk lima kelas dan lima assignment aktif; tidak ada sesi atau rekap historis yang diimport.
- Regresi Admin lulus: 9 test dengan 32 assertion; resume point `IMP-MIG-005` adalah staging batch setelah persetujuan publikasi.
# 2026-09-05 — IMP-MIG-014 atomic checkpoint: master 84 santri dan enrollment
- Menerapkan migration additive: `student_code` resmi nullable, NIS dan NISN opsional; UUID internal aplikasi tetap primary key.
- Mengimpor 84 santri dari CSV dengan nama Indonesia/Arab dipertahankan dan enrollment aktif kelas 1/2A/2B/3A/3B.
- `entry_year` diturunkan dari asumsi tahun ajaran 2026/2027 dan kenaikan satu tingkat per tahun: kelas 1=2026, kelas 2=2025, kelas 3=2024.
- Uji idempotensi lulus: run kedua membuat 0 baris baru dan mengenali 84 sudah terimpor; distribusi kelas 20/19/10/15/20.
- `student_code`, NIS, dan NISN tetap NULL; laporan Juli tetap 5 baris/1126 hadir; tidak ada attendance import.
- Regression lulus: 8 test dengan 41 assertion; PHP lint migration/service lulus.
- Resume point: validasi visual/admin master santri atau siapkan workflow pengisian NIS/NISN; session participant baru belum dibuat untuk histori.
# 2026-09-06 — IMP-MIG-015 atomic checkpoint: monthly student snapshot contract review
- Reviewed the approved concept for `monthly_student_attendance_snapshots` as a per-student monthly aggregate archive, explicitly separate from session/day attendance facts.
- Documented the proposed grain, source lineage, canonical Student mapping gate, preserved Class_Admin/Attendance_Group/MATIQ mapping, eligibility/denominator rules, and batch lifecycle boundary.
- Read-only database inspection confirmed 84 staging roster rows, 84 staging attendance rows, 0 mapped staging `student_id`, and 5 published July class summaries.
- No migration, model, import, route, or production data change was made. Safe resume point: implement the additive schema and mapping validator in a separate checkpoint.
# 2026-09-06 — IMP-MIG-015 atomic checkpoint: snapshot schema and mapping validator
- Added additive `monthly_student_attendance_snapshots` migration with monthly grain, canonical Student/class FKs, import lineage FKs, raw provenance, formula/rate/scheduled-unit checks, and duplicate protections.
- Added `MonthlyStudentAttendanceSnapshot` model and pure `MonthlyStudentAttendanceSnapshotValidator` requiring exact canonical identity/class mapping and rejecting session expansion.
- Verification: targeted validator tests 3/3 with 7 assertions; PHP lint and SQLite migration dry-run PASS.
- Migration was not applied to the application database and no production snapshot data was imported; 84 staging rows remain pending canonical mapping/reconciliation.
# 2026-09-07 — IMP-UI-034 atomic checkpoint: audit istilah UI pesantren
- Mengganti istilah teknis yang tampil kepada pengguna: attendance menjadi data kehadiran, finalisasi menjadi pengesahan, snapshot menjadi rekap historis, serta master/staff menjadi data/staf.
- Tidak mengubah rute, RBAC, database, kontrak, atau business rule; dua assertion fitur disesuaikan dengan label UI baru.
- Pemeriksaan awal: PHP lint dan Blade cache lulus; test terarah menemukan 3 assertion judul lama yang kemudian diperbarui. Verifikasi akhir lulus dan checkpoint aman ditutup.
# 2026-09-07 — IMP-UI-035 atomic checkpoint: Phase 1 UI shell Dashboard Waka Akademik
- Merombak satu view dashboard Academic menjadi shell modern dengan sidebar, header role-aware, filter periode existing, KPI dari payload existing, pemantauan per kelas, rail laporan, dan responsive layout.
- Widget yang belum memiliki source resmi ditampilkan sebagai `Belum tersedia`; tidak ada angka dummy, service baru, migration, RBAC, route, atau perubahan business rule.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion, Blade cache PASS, PHP lint PASS, browser smoke Waka PASS. Phase 2 tidak dimulai.
- Polesan lanjutan Phase 1: menambah ikon semantic pada KPI dan memastikan judul dashboard role-aware; tidak memperluas scope data/backend.
# 2026-09-07 — IMP-UI-035 follow-up: perbaikan jarak antar blok
- Menambah spacing vertikal/horizontal pada header, filter, KPI, panel status, dan rail agar dashboard tidak terasa menempel pada viewport desktop/tablet.
- Verifikasi ulang: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion, Blade cache PASS, dan browser smoke PASS. Tidak memulai Phase 2.
# 2026-09-07 — IMP-UI-035 follow-up: pemisahan label filter
- Memisahkan label “Mulai/Sampai” dari input tanggal secara vertikal dengan gap yang jelas setelah feedback visual owner.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion, Blade cache PASS, browser smoke PASS. Phase 2 tetap belum dimulai.
# 2026-09-07 — IMP-UI-035 follow-up: semantic reminder icons
- Memperbaiki tanda seru dan tanda centang pada panel pengingat dengan warna amber untuk peringatan dan hijau untuk status aman.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion dan Blade cache PASS. Phase 2 tetap belum dimulai.
# 2026-09-07 — IMP-UI-035 follow-up: neutral status badges
- Menjadikan label `Belum tersedia` pada header panel sebagai badge netral agar tidak terbaca seperti tombol tindakan.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion dan Blade cache PASS. Phase 2 tetap belum dimulai.
# 2026-09-07 — IMP-UI-035 follow-up: centering reminder icons
- Memperbaiki konflik selector CSS yang membuat tanda seru/centang tidak benar-benar berada di tengah rounded box.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion dan Blade cache PASS. Phase 2 tetap belum dimulai.
# 2026-09-07 — IMP-UI-035 follow-up: neutral unavailable KPI surface
- Mengubah permukaan kartu `Data santri & guru` menjadi netral agar state belum tersedia tidak terbaca sebagai kategori analitik yang aktif.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion, Blade cache PASS, browser smoke PASS. Phase 2 tetap belum dimulai.
# 2026-09-07 — IMP-UI-035 follow-up: konsistensi kartu KPI
- Menyamakan tinggi minimum kartu KPI dan menempatkan keterangan bawah secara konsisten agar grid visual lebih rapi.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion dan Blade cache PASS. Phase 2 tetap belum dimulai.
# 2026-09-07 — IMP-UI-035 follow-up: semantic neutral state
- Mengubah warna teks state `Belum tersedia` menjadi netral agar tidak terbaca sebagai status berhasil/aktif.
- Verifikasi: AcademicRoleDashboardServiceTest 10/10 dengan 24 assertion, Blade cache PASS, browser smoke PASS. Phase 2 tetap belum dimulai.
# 2026-09-07 — IMP-WAKA-P2-001 atomic checkpoint: supported KPI read model
- Menambahkan overview KPI berbasis data tervalidasi: santri aktif, guru Academic aktif, kelas aktif, kehadiran fisik, dan kelengkapan data.
- Mempertahankan denominator semantics: PRESENT/LATE untuk kehadiran fisik, record VALIDATED untuk kelengkapan, dan denominator nol menjadi `Belum tersedia`.
- Verifikasi: AcademicRoleDashboardServiceTest 11/11 dengan 29 assertion, Blade cache PASS, browser UAT reload PASS. Tidak ada perubahan database, RBAC, route, atau business rule.
# 2026-09-07 — IMP-WAKA-P2-001 follow-up: tren kehadiran harian
- Menambahkan agregasi tren harian dari sesi efektif, peserta wajib, dan StudentAttendance VALIDATED; numerator memakai PRESENT/LATE dan denominator memakai kesempatan wajib.
- Menampilkan grafik batang ringan yang responsif di dashboard tanpa JavaScript atau data sintetis.
- Verifikasi: AcademicRoleDashboardServiceTest 12/12 dengan 35 assertion, Blade cache PASS, browser reload PASS. Phase 3 belum dimulai.
# 2026-09-07 — IMP-WAKA-P2-001 follow-up: status sesi santri hari ini
- Menambahkan status sesi hari ini: akan datang, berlangsung, jatuh tempo belum disahkan, dan sudah disahkan; sesi dibatalkan/di-reschedule tetap dikecualikan.
- State tanpa sesi tetap netral dan tidak mengubah data kosong menjadi ABSENT.
- Verifikasi: AcademicRoleDashboardServiceTest 13/13 dengan 42 assertion, Blade cache PASS, browser reload/UAT PASS. Phase 3 belum dimulai.
# 2026-09-07 — IMP-WAKA-P2-001 follow-up: pilihan rentang grafik
- Menambahkan pilihan rentang grafik 7, 14, dan 30 hari melalui query tervalidasi; filter periode utama tetap dipertahankan.
- Verifikasi: AcademicRoleDashboardServiceTest 14/14 dengan 46 assertion, Blade cache PASS, browser reload PASS. Phase 3 belum dimulai.
# 2026-09-07 — DATA-AUDIT atomic checkpoint: rancangan status santri aktif
- Audit read-only menemukan 94 enrollment aktif efektif 2026-07-01, tetapi belum ada baris `student_status_history`.
- Menyusun `codex/STUDENT_STATUS_BACKFILL_PLAN_2026-09-07.md` sebagai rancangan DRAFT; tidak ada INSERT, migration, atau perubahan data.
- Eksekusi tetap menunggu persetujuan pemilik data bahwa 94 enrollment tersebut memang berstatus ACTIVE.
# 2026-09-07 — DATA-AUDIT correction: pisahkan roster historis dan sample
- Klarifikasi read-only: 84 santri adalah roster historis Juli 2026; 10 baris tambahan adalah sample/pilot (5 Kelas 3A dan 5 Kelas 3B).
- Rancangan backfill dikoreksi menjadi target 84 santri historis; 10 sample/pilot dikecualikan sampai ada keputusan data terpisah.
# 2026-09-07 — DATA-AUDIT dry-run: target 84 tervalidasi
- Dry-run read-only menghasilkan 84 santri historis dan 84 enrollment aktif sebagai target; 10 santri serta 10 enrollment `PILOT-*` dikecualikan.
- Tidak ada INSERT atau perubahan database. Eksekusi backfill masih menunggu persetujuan eksplisit untuk menulis 84 baris status.
# 2026-09-07 — DATA-STATUS-001: backfill 84 status santri historis
- Setelah persetujuan owner, membuat 84 baris `student_status_history` berstatus `ACTIVE` efektif 2026-07-01 dalam satu transaksi.
- 10 sample/pilot dikecualikan; verifikasi status pilot = 0. Dashboard Waka menampilkan 35 santri aktif sesuai scope kelas 3A/3B.
# 2026-09-07 — DATA-AUDIT atomic checkpoint: rancangan tugas mengajar
- Audit read-only menemukan 7 staf aktif tetapi 0 `TeachingAssignment` aktif; semester dan satu mata pelajaran yang tersedia masih berlabel sample/pilot.
- Menyusun `codex/TEACHING_ASSIGNMENT_PLAN_2026-09-07.md`; tidak membuat assignment, jadwal, sesi, atau migration.
# 2026-09-07 — IMP-SCH-001 atomic checkpoint: workflow publikasi jadwal
- Menambahkan validator dan service publikasi dengan alur `DRAFT -> VALIDATED -> PUBLISHED`; validasi memeriksa guru, mata pelajaran, dan scope kelas.
- Menambahkan endpoint admin dan panel tindakan pada halaman jadwal resmi; RBAC existing tetap dipakai dan setiap transisi assignment/rule dicatat ke audit log.
- Tidak menerbitkan data resmi pada checkpoint ini; 51 schedule rule dan assignment resmi tetap `DRAFT`, attendance tidak berubah.
- Verifikasi: publication workflow + validator + importer + scope tests lulus 6/6 dengan 27 assertion; route list menampilkan endpoint validate/publish.
- Safe resume point: lakukan validasi resmi sebagai langkah terpisah, lalu review hasil sebelum publikasi.
# 2026-09-07 — IMP-SCH-001 follow-up: validasi jadwal resmi
- Validasi resmi dijalankan untuk Semester 1 Tahun Ajaran 2026/2027: 51 schedule rule dan 51 teaching assignment berubah dari `DRAFT` menjadi `VALIDATED`.
- Audit tercatat 102 baris, yaitu satu audit untuk setiap rule dan assignment. Dua jadwal pilot berstatus `APPROVED` tidak ikut berubah.
- Tidak ada publikasi, pembuatan attendance, atau perubahan struktur database pada langkah ini.
- Safe resume point: review hasil validasi, lalu pilih apakah jadwal resmi siap diterbitkan.
# 2026-09-07 — IMP-SCH-001 follow-up: publikasi jadwal resmi
- 51 schedule rule dan 51 teaching assignment resmi diterbitkan setelah validasi berhasil.
- Audit publikasi tercatat 102 baris, yaitu satu audit untuk setiap rule dan assignment.
- Tidak ada pembuatan atau perubahan transaksi kehadiran; 1.261 session resmi dan scope-nya tetap menjadi output generator terpisah.
- Safe resume point: verifikasi tampilan jadwal published dan uji generator/session setelah status published.
# 2026-09-07 — IMP-SCH-001 verification: tampilan jadwal published
- Browser smoke sebagai Super Admin berhasil membuka halaman Data Jadwal.
- Halaman menampilkan 53 baris: 51 jadwal resmi berstatus `PUBLISHED` dan 2 jadwal pilot lama berstatus `Disetujui`.
- Ditemukan gap presentasi: banner masih menyatakan jadwal menunggu validasi dan tombol validasi/terbitkan tetap tampil setelah status resmi published.
- Tidak ada perubahan code atau data pada langkah verifikasi; safe resume point adalah memperbaiki state UI workflow tersebut.
# 2026-09-07 — IMP-SCH-001 follow-up: perbaikan pagination dan state published
- Memperbaiki ikon pagination Laravel yang membesar dengan styling lokal pada `nav[role="navigation"]`, termasuk ukuran ikon dan tombol halaman.
- State resmi published sekarang menampilkan pesan bahwa jadwal siap digunakan, menyembunyikan tombol validasi/publikasi, dan menerjemahkan status `PUBLISHED` menjadi `Diterbitkan`.
- Verifikasi: `ScheduleRuleAdminTest` 5/5 dengan 17 assertion, Blade cache dan PHP lint lulus, browser smoke menunjukkan pagination normal serta status published yang konsisten.
- Tidak ada perubahan database, RBAC, route, atau business rule.
# 2026-09-07 — IMP-SCH-001 follow-up: perapian form edit/tambah jadwal
- Menata form jadwal menjadi grid dua kolom pada desktop dan satu kolom pada layar kecil.
- Menambahkan ringkasan assignment, kelas, dan mata pelajaran pada halaman edit; memperjelas fieldset pilihan minggu dan jarak antar kontrol.
- Verifikasi: `ScheduleRuleAdminTest` 5/5 dengan 17 assertion, Blade cache/PHP lint lulus, browser smoke halaman Edit Jadwal lulus.
- Tidak ada perubahan database, RBAC, route, atau business rule.
# 2026-09-07 — IMP-SCH-001 follow-up: keterbacaan daftar jadwal
- Mengubah label tabel menjadi lebih mudah dipahami: `Mata pelajaran`, `Berlaku`, `Status jadwal`, dan `Lihat / edit`.
- Menambahkan ringkasan jumlah jadwal, penjelasan satu baris, status berwarna, periode tanggal dua baris, zebra row, serta kolom aksi yang jelas.
- Verifikasi: `ScheduleRuleAdminTest` 5/5 dengan 17 assertion, Blade cache lulus, browser smoke menunjukkan tabel lebih mudah dipindai.
- Tidak ada perubahan database, RBAC, route, atau business rule.
# 2026-09-07 — DATA-UI correction: pisahkan kelas resmi dan histori pilot
- Audit read-only menemukan 10 kelas: 5 kelas resmi `2026/2027` dan 5 kelas pilot `2026/2027-PILOT`; pilot memiliki enrollment/sesi historis sehingga tidak dihapus.
- Daftar kelas admin sekarang menampilkan hanya tahun ajaran resmi aktif non-pilot dan memberi penjelasan bahwa data pilot tetap tersimpan sebagai histori.
- Verifikasi: daftar browser menampilkan tepat 5 kelas resmi; test tambahan memastikan `PILOT-CLASS` tidak masuk daftar operasional. `ScheduleRuleAdminTest` 6/6 dengan 21 assertion.
- Tidak ada delete, perubahan data historis, perubahan database schema, RBAC, route, atau business rule.
# 2026-09-07 — DATA-UI follow-up: pemisahan santri dan struktur resmi
- Halaman Struktur sekarang hanya menampilkan unit IMTAQ ISY KARIMA, tingkat 1–3, dan lima kelas resmi; unit/tingkat Sample School tidak lagi bercampur.
- Halaman Santri tetap menampilkan 84 roster historis karena enrollment mereka masih berada pada kelas pilot; data tidak disembunyikan sebelum pemetaan enrollment resmi dilakukan.
- Opsi tambah/edit santri dibatasi ke kelas resmi untuk mencegah enrollment baru masuk ke kelas pilot.
- Verifikasi: 10 test admin lulus dengan 36 assertion, Blade cache/PHP lint lulus, browser smoke menunjukkan 84 santri dan struktur resmi bersih.
- Tidak ada perubahan data database, penghapusan histori, schema, RBAC, route, atau business rule.
# 2026-09-07 — DATA-UI follow-up: pemisahan Guru/Staf resmi dan histori pilot
- Audit read-only menemukan 24 staf aktif: 17 staf resmi berkode operasional dan 7 staf pilot berkode `PILOT-*`; relasi histori pilot tetap dipertahankan.
- Daftar Guru/Staf admin sekarang menampilkan 17 staf resmi dan memberi informasi bahwa 7 data pilot lama tidak dicampur dalam daftar operasional.
- Verifikasi browser menampilkan jumlah 17 staf resmi; test admin 10/10 dengan 37 assertion, Blade cache dan PHP lint lulus.
- Tidak ada penghapusan data, perubahan schema, RBAC, route, atau business rule.
# 2026-09-07 — DATA-AUDIT checkpoint: rekonsiliasi Wali Kelas resmi
- Audit read-only menunjukkan lima kelas resmi Tahun Ajaran 2026/2027 belum memiliki assignment Wali Kelas aktif.
- Nama yang diberikan owner cocok dengan empat record staf berkode `PILOT-*`: Ust. Alwan, Ust. Afwa, Azhar, dan Ust. Maulana; belum ada identitas staf resmi non-pilot untuk keempatnya.
- Tidak membuat assignment menggunakan identitas pilot agar data resmi dan histori pilot tetap terpisah. Pemetaan resmi menunggu penyediaan/konfirmasi identitas staf resmi.
- Tidak ada perubahan database, schema, RBAC, route, atau business rule.
# 2026-09-07 — DATA-STRUCTURE: pemetaan Wali Kelas resmi disetujui owner
- Menambahkan seeder idempoten `OfficialAcademicStaffSeeder` untuk lima identitas staf resmi: `ALW`, `AFW`, `AZH`, `MLN`, `WAWAN`.
- Memetakan kelas resmi: 1→ALW, 2A→AFW, 2B→MLN, 3A→AZH, 3B→MLN; record pilot tetap dipertahankan.
- Eksekusi seed dan verifikasi dilakukan setelah persetujuan owner; tidak mengubah schema, RBAC, route, atau attendance facts.
# 2026-09-07 — DATA-STRUCTURE verification: Wali Kelas resmi aktif
- Database lokal berisi 5 staf resmi dan 5 assignment aktif: Kelas 1→ALW, 2A→AFW, 2B→MLN, 3A→AZH, 3B→MLN.
- Browser smoke menampilkan seluruh pemetaan resmi pada halaman Tingkat & Wali Kelas; 7 record staf pilot tetap ada.
- Verifikasi: 12 test lulus dengan 44 assertion, seeder idempoten lulus, Blade cache/PHP lint lulus.
# 2026-09-08 — DATA-AUDIT checkpoint: akun Waka resmi belum tersedia
- Read-only audit menemukan staf resmi `WAWAN` sudah ada, tetapi satu-satunya akun ber-role `WAKA_AKADEMIK` masih `wawan.sn.pilot@example.test` dan terhubung ke `PILOT-WAWAN-SN`.
- Tidak memindahkan link akun pilot ke staf resmi dan tidak membuat kredensial baru tanpa email resmi/keputusan owner.
- Tidak ada perubahan database, RBAC, route, atau business rule pada checkpoint ini.
# 2026-09-08 — DATA-STRUCTURE: akun Waka resmi dibuat
- Seeder resmi sekarang membuat/memperbarui akun `whyarby72@gmail.com` sebagai Ust. Wawan, menghubungkannya ke staf `WAWAN`, dan memberi role `WAKA_AKADEMIK` efektif 2026-07-01.
- Kata sandi awal mengikuti standar seed development lokal: `password`; wajib diganti sebelum penggunaan nyata.
- Akun pilot dan link `PILOT-WAWAN-SN` tidak diubah.
# 2026-09-08 — DATA-STRUCTURE verification: akun Waka resmi
- Verifikasi read-only memastikan email `whyarby72@gmail.com`, password awal valid, role `WAKA_AKADEMIK`, dan link staf `WAWAN` tersimpan.
- Verifikasi: 9 test lulus dengan 36 assertion, seeder idempoten lulus, Blade cache/PHP lint lulus.
# 2026-09-08 — DATA-STRUCTURE UAT: dashboard Waka dapat dibuka
- Browser smoke pada `/academic/dashboard` menampilkan `Dashboard Waka Akademik`, navigasi akademik, ringkasan KPI, pemantauan kelas, dan laporan.
- Sesi browser yang terbuka sudah berada pada tampilan role Waka; verifikasi kredensial akun resmi dilakukan melalui database sebelumnya.
# 2026-09-08 — IMP-WAKA-AUDIT: cakupan dashboard resmi
- Audit menemukan dashboard Waka semula mencampur dua kelas pilot dan tidak menampilkan kelas resmi 3B karena sesi langsungnya tidak ada.
- Query cakupan diperbaiki: Waka/Super Admin hanya memakai kelas pada tahun ajaran non-pilot yang beririsan dengan periode, sehingga kelas resmi tanpa sesi langsung tetap terlihat sebagai `Belum tersedia`/`0/0`.
- Verifikasi browser sekarang menampilkan tepat 5 kelas resmi: Kelas 1, 2A, 2B, 3A, dan 3B; akses laporan bulanan Waka juga berhasil.
- Verifikasi: AcademicRoleDashboardServiceTest + admin tests 25/25 dengan 86 assertion, Blade cache/PHP lint lulus. Tidak ada perubahan schema, RBAC, route, atau attendance semantics.
# 2026-09-07 — DATA-UI follow-up: pilihan Wali Kelas hanya staf resmi
- Dropdown tambah Wali Kelas dan pilihan pada halaman edit sekarang mengecualikan staf berkode `PILOT-*`; staf pilot yang sedang terpasang tetap dipertahankan dan tetap terlihat saat mengedit histori tersebut.
- Menambahkan penjelasan UI bahwa penetapan pilot lama tetap tersimpan sebagai histori.
- Verifikasi: 11 test admin lulus dengan 40 assertion, Blade cache/PHP lint lulus, browser smoke menampilkan hanya pilihan staf resmi.
- Tidak ada penghapusan data, perubahan schema, RBAC, route, atau business rule.
# 2026-09-08 — DATA-RECON: dry-run enrollment historis Juli
- Dry-run menemukan 94 enrollment aktif di kelas pilot: 84 sumber `IMTAQ-MASTER-2026-07:*` dan 10 sumber `SAMPLE-PILOT`.
- Target rekonsiliasi ditetapkan hanya 84 enrollment master; 10 sample tetap berada di histori pilot.
# 2026-09-08 — DATA-RECON: pindahkan 84 enrollment ke kelas resmi
- Menambahkan seeder idempoten `OfficialStudentEnrollmentReconciliationSeeder` yang memetakan enrollment master berdasarkan kode kelas pilot ke kelas resmi 2026/2027.
- `source_reference` dipertahankan, `reason` dan `version_no` diperbarui sebagai lineage perubahan; snapshot dan fakta kehadiran tidak disentuh.
- Verifikasi database: kelas pilot tersisa 10 enrollment sample, kelas resmi berisi 84 enrollment master; dashboard Waka menampilkan 84 santri aktif.
- Verifikasi: 26 test lulus dengan 89 assertion, seeder idempoten lulus, Blade cache/PHP lint lulus, browser smoke lulus.
# 2026-09-08 — DATA-RECON: snapshot peserta sesi resmi
- Dry-run menemukan 1.261 sesi resmi tanpa peserta dan 4 sesi pilot terpisah.
- Menambahkan `OfficialSessionParticipantReconciliationSeeder` yang menggunakan `SessionParticipantSnapshotter` existing untuk membuat peserta wajib dari enrollment resmi; sesi pilot, status kehadiran, dan histori attendance tidak disentuh.
- Hasil database: 26.201 peserta pada 1.261 sesi resmi; peserta pilot existing tetap 20.
- Dashboard Waka sekarang menampilkan 84 santri, 4.339 kesempatan periode September, dan kelengkapan 0% karena status kehadiran belum diisi—bukan data hadir/absen yang dibuat otomatis.
- Verifikasi: 30 test lulus dengan 104 assertion, seeder idempoten lulus, Blade cache/PHP lint lulus, browser smoke lulus.
# 2026-09-08 — IMP-WAKA-AUDIT follow-up: metrik sesi gabungan
- Metrik kehadiran dan sesi sekarang membaca `class_session_groups`; peserta sesi gabungan difilter berdasarkan enrollment kelas agar tidak terhitung silang.
- Dashboard Waka sekarang menampilkan Kelas 3B dengan 1.020 kesempatan periode September; Kelas 2B menampilkan 510 kesempatan gabungan/direct sesuai scope.
- Menambahkan `JointClassMetricsTest` untuk memastikan sesi gabungan dihitung pada tiap kelas tanpa menggandakan santri.
- Verifikasi: 20 test terkait lulus dengan 70 assertion, Blade cache/PHP lint lulus, browser smoke lulus. Attendance facts tidak berubah.
# 2026-09-08 — IMP-WALI-AUDIT: cakupan dashboard Wali Kelas
- Read-only audit menemukan satu akun Wali Kelas: `azhar.pilot@example.test`, terhubung ke `PILOT-AZHAR` dan saat ini melihat kelas pilot `3A` dengan 0 santri resmi.
- Lima assignment Wali Kelas resmi sudah tersedia untuk staf resmi `ALW`, `AFW`, `AZH`, dan `MLN`, tetapi belum ada akun/link Wali Kelas resmi untuk digunakan login.
- Tidak ada perubahan code atau database pada audit ini. Perlu keputusan apakah membuat akun resmi Wali Kelas dan link staf resmi, serta menambahkan filter dashboard agar kelas pilot tidak pernah masuk cakupan operasional.
# 2026-09-08 — IMP-WALI: akun resmi lokal dan filter kelas pilot
- Menambahkan `OfficialWaliAccountSeeder` dengan akun lokal `.example.test` untuk Ust. Alwan, Afwa, Azhar, dan Maulana; akun pilot tidak diubah.
- Dashboard Wali Kelas sekarang membatasi kelas pada tahun ajaran non-pilot yang beririsan dengan periode laporan.
- Seeder berhasil dijalankan: ALW melihat Kelas 1, AFW Kelas 2A, AZH Kelas 3A, dan MLN Kelas 2B serta 3B.
- Verifikasi: `AcademicRoleDashboardServiceTest` 17/17 lulus, termasuk regression test pilot-scope; tidak ada perubahan schema, route, RBAC model, atau attendance semantics.
# 2026-09-08 — RBAC-AUDIT: konsolidasi tiga role utama
- Read-only audit menemukan empat role aplikasi: `SUPER_ADMIN`, `WAKA_AKADEMIK`, `WALI_KELAS`, dan `ADMIN_AKADEMIK`.
- `ADMIN_AKADEMIK` masih dipakai oleh controller master akademik (kelas, staf, santri, struktur, jadwal, dashboard admin), sehingga belum aman dihapus tanpa migrasi otorisasi.
- Role yang aktif pada database lokal: 1 `SUPER_ADMIN`, 2 `WAKA_AKADEMIK`, 5 `WALI_KELAS`, dan 1 `ADMIN_AKADEMIK`; role belum memakai permission matrix efektif, sebagian besar authorization masih hard-coded pada controller/service.
- Belum ada implementasi domain Tahfizh/Kesantrian pada `app/Domains`; pemisahan lintas segmen perlu disiapkan pada boundary authorization sebelum domain-domain tersebut aktif.
- Tidak ada perubahan code atau database pada audit ini.
# 2026-09-08 — IMP-RBAC-002: konsolidasi role Akademik menjadi tiga role
- Jalur admin Akademik dialihkan dari `ADMIN_AKADEMIK` ke `WAKA_AKADEMIK`; Wali Kelas tetap terbatas pada scope kelas yang ditetapkan.
- Permission export dashboard dan handover attendance historis diberikan kepada role Waka melalui seeder konsolidasi.
- Assignment pilot Wali/Waka dicabut; assignment resmi tetap: 4 Wali Kelas, 1 Waka Akademik, dan 1 Super Admin. Role `ADMIN_AKADEMIK` dihapus setelah assignment kosong; akun pilotnya tidak dihapus.
- Verifikasi: 175 test lintas Academic/Admin lulus dengan 590 assertion; data attendance dan schema tidak berubah.
- Browser smoke: Waka berhasil login ke `/admin/academic`; Wali berhasil login ke `/academic/dashboard` dan hanya melihat Kelas 3A; akses Wali ke `/admin/academic` ditolak 403; Super Admin berhasil login ke `/admin/academic`.
- Follow-up UI audit: dashboard dan laporan Wali tidak menampilkan menu admin; direct access Wali ke dashboard/classes/staff/structure/schedules admin seluruhnya 403. Ditemukan dua isu UX non-security: redirect pasca-login Wali masih menuju `/admin/academic`, dan pesan 403 dashboard masih menyebut `Academic Admin`.
# 2026-09-08 — IMP-RBAC-003: alur login dan menu Wali Kelas
- Login sekarang mengarahkan Wali Kelas langsung ke dashboard operasional; Waka dan Super Admin tetap diarahkan ke Admin Akademik.
- Pesan penolakan admin diperbarui menjadi `Waka Akademik atau Super Admin`.
- Regression test menegaskan Wali tidak melihat tautan admin dan tetap menerima 403 pada dashboard admin.
- Verifikasi: 24 test lulus dengan 79 assertion; browser smoke redirect Wali dan halaman 403 lulus.
# 2026-09-08 — IMP-WALI-UAT: audit alur kehadiran end-to-end
- Browser Wali membuka sesi resmi Kelas 3A dengan 15 peserta, melihat tombol `Tandai semua hadir`, `Simpan sementara`, dan `Sahkan kehadiran`.
- Browser Wali membuka sesi Kelas 1 di luar scope dan menerima 403: bukan Wali Kelas efektif untuk sesi tersebut.
- Tidak ada draft atau finalisasi yang dikirim selama audit read-only.
- Verifikasi: `StudentAttendanceUiTest`, `StudentAttendanceDraftServiceTest`, `StudentAttendanceFinalizerTest`, dan `WaliKelasContextResolverTest` lulus 13/13 dengan 44 assertion.
# 2026-09-08 — IMP-WALI-UI: akses pengisian dari dashboard
- Dashboard Wali sekarang memuat panel `Pengisian Kehadiran` berisi hingga 12 sesi pada periode terpilih, masing-masing dengan kelas, waktu, jumlah santri, dan tautan `Isi kehadiran`.
- Data sesi berasal dari `ClassSession` resmi yang tidak dibatalkan; Waka/Super Admin tidak mendapat tombol pengisian karena endpoint transaksi tetap khusus Wali.
- Verifikasi: 20 test lulus dengan 65 assertion; browser Wali menampilkan 12 sesi Kelas 3A dan tautan ke formulir attendance.
- Tidak ada status kehadiran yang dibuat atau diubah.
# 2026-09-08 — IMP-WALI-UI follow-up: istilah Santri
- Mengganti seluruh label `Siswa` menjadi `Santri` pada formulir kehadiran: judul, ringkasan total, petunjuk, dan header tabel.
- Verifikasi: `StudentAttendanceUiTest` 2/2 lulus dengan 9 assertion, Blade cache lulus, dan browser smoke menampilkan `Kehadiran Santri` serta `Total santri`.
# 2026-09-08 — IMP-WALI-UI-REPEAT: membedakan sesi pengisian kehadiran
- Daftar sesi Wali Kelas sekarang menampilkan mata pelajaran, tanggal/jam, jumlah santri, status pengisian, dan tindakan yang sesuai.
- Sesi yang berbeda tidak dihapus atau digabung; tampilan diperjelas agar sesi `Belum diisi`, `Belum lengkap`, dan `Sudah disahkan` mudah dibedakan.
- Verifikasi: 20 test dashboard/attendance lulus dengan 67 assertion, Blade cache lulus, dan browser smoke menampilkan mata pelajaran serta status per sesi.
- Tidak ada perubahan database, RBAC, route, session, atau attendance data.
# 2026-09-08 — IMP-WALI-UI-REPEAT follow-up: pengelompokan per tanggal
- Daftar sesi Wali Kelas sekarang memiliki pemisah `Tanggal dd/mm/yyyy` sebelum kelompok sesi pada tanggal yang sama.
- Format tanggal dibuat netral agar tidak bergantung pada locale server dan tetap mudah dipahami di UI pesantren.
- Verifikasi: 20 test lulus dengan 68 assertion, Blade cache lulus, dan browser smoke menampilkan kelompok tanggal.
# 2026-09-08 — IMP-WALI-UI-REPEAT follow-up: filter status pengisian
- Menambahkan filter tampilan `Semua`, `Belum diisi`, `Belum lengkap`, dan `Sudah disahkan` pada panel Pengisian Kehadiran.
- Filter mempertahankan periode, route, dan data attendance existing; tidak melakukan write ke database.
- Verifikasi: 20 test lulus dengan 71 assertion, Blade cache lulus, dan browser smoke filter status berhasil.
# 2026-09-08 — IMP-WALI-UI follow-up: headline sesi memakai pelajaran
- Pada panel Wali Kelas, nama pelajaran sekarang menjadi headline kartu sesi; label kelas dihilangkan karena cakupan kelas Wali sudah otomatis terbatas pada kelas tanggung jawabnya.
- Tanggal, waktu, jumlah santri, status, dan tindakan tetap ditampilkan sebagai informasi pendukung.
- Tidak ada perubahan data, database, RBAC, route, atau attendance semantics.
# 2026-09-08 — IMP-WALI-UI follow-up: ringkasan jumlah sesi
- Filter status pengisian sekarang menampilkan jumlah sesi pada setiap pilihan, misalnya `Belum diisi (11)` dan `Belum lengkap (1)`.
- Perhitungan berasal dari daftar sesi yang sudah ditampilkan dashboard; tidak membuat angka atau transaksi baru.
- Verifikasi: 20 test lulus dengan 71 assertion, Blade cache lulus, dan browser smoke ringkasan status berhasil.
# 2026-09-08 — IMP-WAKA-KPI: penyelarasan KPI Dashboard Waka
- KPI dashboard admin sekarang membatasi kelas, guru, dan jadwal ke data akademik resmi non-pilot.
- Menambahkan KPI `Santri terdata` yang membaca snapshot Juli 2026 dan menampilkan basis 84 santri.
- Laporan Juli tetap menjadi sumber rekap bulanan; fakta kehadiran operasional tidak diubah.
# 2026-09-08 — IMP-WAKA-UI: label dashboard Waka
- Mengganti label tampilan `Dashboard Admin Akademik` menjadi `Dashboard Waka Akademik` pada halaman utama Waka.
- Route, otorisasi, menu, dan fungsi admin akademik tetap sama; perubahan hanya memperjelas identitas pengguna.
# 2026-09-08 — IMP-WAKA-UI-TERMS: konsistensi istilah halaman akademik
- Mengganti label `Admin Akademik` menjadi `Waka Akademik` pada halaman Struktur, Kelas, Guru/Staf, Jadwal, dan Santri.
- Memperbarui keterangan operasional yang sebelumnya menyebut pengelolaan melalui Admin Akademik.
- Tidak ada perubahan route, RBAC, database, service, atau business rule.
- Audit visual menemukan dan memperbaiki sisa teks `dari Dashboard Admin` pada halaman Struktur menjadi `dari Dashboard Waka Akademik`.
# 2026-09-08 — IMP-WAKA-UI-LOGIN: label login netral
- Mengganti label login `Admin Akademik` menjadi `Akses Sistem IMTAQ` karena login digunakan oleh Super Admin, Waka Akademik, dan Wali Kelas.
- Tidak mengubah autentikasi, redirect role, route, RBAC, database, atau business rule.
- Browser smoke: Super Admin dan Waka masuk ke dashboard akademik; Wali masuk ke dashboard Wali, melihat panel Pengisian Kehadiran, dan menerima 403 saat membuka dashboard admin.
# 2026-09-08 — IMP-WAKA-UI-MOBILE: audit responsif
- Audit viewport mobile meninjau dashboard Wali, layout KPI, filter, panel status, agenda, dan transformasi tabel laporan.
- Breakpoint responsif existing dinilai memadai; tidak ada perubahan CSS atau business logic pada checkpoint ini.
- Validasi test dan Blade cache tetap lulus.
# 2026-09-08 — IMP-UAT-ROLES: smoke operasional tiga role
- Super Admin dan Waka Akademik berhasil login ke `/admin/academic`; Wali Kelas berhasil login ke `/academic/dashboard`.
- Wali Kelas melihat panel Pengisian Kehadiran dan menerima 403 saat mencoba dashboard admin.
- Tidak ada transaksi kehadiran atau perubahan data yang dilakukan selama smoke test.
# 2026-09-08 — IMP-UAT-WALI-ATTENDANCE: simpan sementara
- UAT pada satu sesi resmi Kelas 3A berhasil mengubah 15 santri menjadi `Hadir` menggunakan helper `Tandai semua hadir`.
- `Simpan sementara` berhasil; halaman menampilkan 15 hadir, 0 tidak hadir, 0 belum diisi, dan tetap menyediakan `Sahkan kehadiran`.
- Data tidak difinalisasi; masih dapat diedit oleh Wali Kelas.
# 2026-09-08 — IMP-UAT-WALI-ATTENDANCE: koreksi izin dan catatan
- Satu santri pada sesi draf yang sama diubah menjadi `Izin` dengan catatan `Izin keluarga pada sesi ini.`.
- `Simpan sementara` berhasil; ringkasan berubah menjadi 14 hadir dan 1 izin, tanpa pengesahan sesi.
# 2026-09-08 — IMP-UAT-WALI-ATTENDANCE: pengesahan sesi
- Sesi resmi Kelas 3A yang sudah lengkap berhasil disahkan oleh Wali Kelas.
- Sistem menampilkan `Kehadiran berhasil disahkan`, status sesi selesai, dan 15 baris menjadi `Sudah diperiksa`.
- Regression: UI, draft service, finalizer, dan resolver Wali Kelas lulus 13 test dengan 44 assertion.
# 2026-09-08 — IMP-WAKA-REVIEW: pemeriksaan read-only hasil sesi
- Menambahkan route pemeriksaan read-only untuk Waka Akademik dan Super Admin pada sesi yang sudah berstatus selesai.
- Tampilan menampilkan status, catatan, dan status pemeriksaan per santri tanpa form simpan atau pengesahan.
- Wali Kelas tetap ditolak dari route pemeriksaan; endpoint pengisian dan finalisasi Wali tidak berubah.
- Verifikasi: 4 test lulus dengan 16 assertion, Blade cache lulus, dan browser smoke Waka menampilkan 15 santri dengan 14 Hadir, 1 Izin, serta seluruh baris Sudah diperiksa.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: daftar sesi selesai dan filter
- Menambahkan halaman daftar seluruh sesi selesai dengan filter kelas, tanggal mulai, dan tanggal akhir.
- Daftar hanya mengambil sesi akademik resmi berstatus selesai, menampilkan mata pelajaran, ringkasan status, dan tautan Periksa hasil.
- Filter kelas pilot dikeluarkan dari pilihan; dashboard Waka menyediakan tautan menuju daftar ini.
- Verifikasi: Admin Dashboard dan Student Attendance UI lulus 7 test dengan 26 assertion, Blade cache lulus, dan browser menampilkan Kelas 3A, Ulumul Qur'an & Hadits, serta ringkasan 14 Hadir dan 1 Izin.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: pagination daftar sesi
- Daftar sesi selesai sekarang menampilkan maksimal 10 sesi per halaman.
- Filter kelas dan tanggal dipertahankan saat berpindah halaman melalui query string.
- Verifikasi: Admin Dashboard dan Student Attendance UI lulus 7 test dengan 26 assertion, Blade cache lulus, dan browser daftar sesi tampil normal.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: tautan dari Dashboard Waka
- Panel Aktivitas Terbaru pada Dashboard Waka sekarang memprioritaskan sesi yang sudah selesai dan menampilkan tautan Periksa hasil.
- Tautan mengarah ke halaman review read-only; sesi yang belum selesai tetap diberi label Belum selesai.
- Verifikasi: Admin Dashboard dan Student Attendance UI lulus 6 test dengan 22 assertion, Blade cache lulus, dan browser Waka menampilkan sesi Kelas 3A dengan tautan pemeriksaan.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: ringkasan status per sesi
- Sesi selesai pada Dashboard Waka sekarang menampilkan jumlah Hadir, Izin, Tidak hadir, dan Belum diisi.
- Ringkasan dihitung dari relasi kehadiran santri yang sudah ada; tidak membuat snapshot atau transaksi baru.
- Verifikasi: Admin Dashboard dan Student Attendance UI lulus 6 test dengan 22 assertion, Blade cache lulus, dan browser menampilkan Hadir 14 · Izin 1 · Tidak hadir 0 · Belum diisi 0.
# 2026-09-08 — IMP-UAT-WAKA-REVIEW: pemeriksaan hasil sesi disahkan
- Login read-only sebagai Waka Akademik berhasil dan dashboard menampilkan 84 santri serta komposisi operasional yang konsisten.
- Akses langsung Waka ke detail sesi yang sudah disahkan ditolak 403 karena endpoint detail saat ini hanya mengenali Wali Kelas efektif untuk sesi tersebut.
- Tidak ada status, data attendance, atau konfigurasi yang diubah selama pemeriksaan.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: ekspor sesi selesai
- Menambahkan ekspor CSV dan PDF untuk seluruh hasil filter sesi selesai.
- Verifikasi: 7 test lulus dengan 33 assertion, Blade cache lulus, dan browser menampilkan tautan Unduh CSV serta Unduh PDF.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: QA ekspor terfilter
- Browser smoke sebagai Waka menguji halaman dengan filter 01–30 September 2026.
- Tautan CSV dan PDF mempertahankan parameter filter; kedua unduhan dapat dipicu tanpa mengubah halaman atau data.
- Hasil daftar tetap konsisten: 1 sesi selesai, Kelas 3A, Ulumul Qur'an & Hadits, 14 Hadir, dan 1 Izin.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: penyempurnaan PDF
- PDF daftar sesi selesai kini memiliki tanggal cetak, nomor halaman, identitas pemeriksa, dan area tanda tangan.
- Verifikasi: 7 test lulus dengan 34 assertion, Blade cache lulus, dan unduhan PDF terbaru berhasil dipicu melalui browser.
# 2026-09-08 — IMP-WAKA-REVIEW follow-up: ekspor detail per-santri
- Halaman pemeriksaan sesi kini menyediakan Unduh detail CSV dan Unduh detail PDF per sesi.
- Detail memuat nama/kode santri, status, catatan, dan status pemeriksaan.
- Verifikasi: 7 test lulus dengan 42 assertion, Blade cache lulus, dan browser menampilkan kedua tautan detail.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL: ekspor rincian laporan Juli per santri
- Halaman detail laporan bulanan Juli kini menyediakan Unduh CSV dan Unduh PDF untuk rincian setiap santri per kelas.
- Ekspor membaca `monthly_student_attendance_snapshots` sebagai snapshot historis; tidak menghitung ulang dan tidak mengubah transaksi kehadiran.
- Encoding PDF diperkuat agar nama Arab tidak menggagalkan unduhan pada font PDF yang tersedia.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan browser menampilkan 15 santri serta kedua tautan ekspor pada Kelas 3A.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: fallback nama Arab pada PDF
- Nama Arab pada PDF tidak lagi hilang diam-diam; generator ringan menggunakan transliterasi Latin deterministik.
- Halaman web tetap menampilkan nama Arab asli dari master santri.
- Verifikasi: 8 test lulus dengan 49 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: engine PDF UTF-8 Arab
- Ekspor PDF detail beralih ke mPDF dengan font Unicode dan shaping Arab, termasuk pagination otomatis.
- Ditambahkan dependency `mpdf/mpdf`; tidak ada perubahan database, RBAC, atau business rule.
- Verifikasi: 8 test lulus dengan 48 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: format cetak resmi
- PDF detail dipoles untuk cetak: margin A4 landscape, ringkasan kelas, header tabel berulang, footer halaman, dan ruang pengesahan.
- Render visual menunjukkan huruf Arab, tabel, warna, spacing, dan ruang pengesahan tampil rapi.
- Verifikasi akhir: 8 test lulus dengan 48 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: identitas organisasi pada PDF
- Menambahkan masthead `IM` dan wordmark teks `IMTAQ ISY KARIMA` pada PDF.
- Aset logo gambar resmi belum tersedia di repository, sehingga belum diganti dengan artwork resmi.
- Verifikasi: 8 test lulus dengan 48 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: logo resmi
- Menambahkan logo resmi yang diberikan pengguna ke `public/images/logo-imtaq.png` dan memasukkannya ke masthead PDF.
- Render visual menunjukkan logo, tabel Arab, ringkasan, dan area pengesahan tampil rapi.
- Verifikasi akhir: 8 test lulus dengan 48 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL: QA lima kelas
- Smoke test menghasilkan PDF detail untuk Kelas 3A, 3B, 1, 2A, dan 2B; seluruhnya memakai logo resmi, font UTF-8, tabel berulang, dan footer halaman.
- Hasil: 3A 15 snapshot/2 halaman, 3B 20/2 halaman, 1 20/2 halaman, 2A 19/2 halaman, dan 2B 10/1 halaman.
- Browser smoke sebagai Waka memicu unduhan CSV dan PDF untuk kelima kelas tanpa perubahan database.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: logo PDF rekap utama
- PDF rekap utama bulanan diseragamkan ke engine mPDF dengan logo resmi, metadata Tahun Ajaran 2026/2027, tabel profesional, total, catatan, dan footer halaman.
- Smoke test terhadap data laporan menghasilkan PDF 212.668 byte dengan 2 objek gambar ter-embed dan pagination mPDF aktif.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: header logo berdampingan
- Header PDF rekap utama dan detail diubah menjadi layout dua kolom: logo resmi di kiri, judul dan metadata laporan di kanan.
- Smoke test database: PDF rekap utama 212.700 byte dan PDF detail Kelas 3A 211.116 byte; keduanya meng-embed logo resmi.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: proporsi logo header
- Ukuran logo pada header PDF diperkecil dari 26 mm menjadi 18 mm dan lebar kolom disesuaikan agar seimbang dengan judul serta tagline.
- Berlaku konsisten untuk PDF rekap utama dan PDF detail per kelas.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: PDF untuk wali santri/pusat
- PDF detail disederhanakan dengan hanya menampilkan Nama Santri, Hadir, Izin, Sakit, Absen, dan Keaktifan.
- Kolom Nama Arab dan Sumber dihapus dari PDF; CSV internal dan data snapshot tidak berubah.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: hilangkan label Pilot
- Label “(Pilot)” dihapus dari metadata nama kelas pada PDF detail agar dokumen siap dibagikan sebagai laporan resmi.
- Identitas kelas di database dan tampilan internal tidak diubah.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: PDF portrait satu halaman
- PDF detail per kelas diubah ke A4 portrait dengan margin, ukuran logo, judul, ringkasan, dan padding tabel yang dipadatkan agar daftar santri kelas dapat dibaca dalam satu halaman.
- PDF rekap utama tetap A4 landscape karena memuat lebih banyak kolom ringkasan.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: ruang tanda tangan
- Jarak antarbaris tabel detail sedikit diperlebar dengan padding baris 6 px.
- Blok tanda tangan diturunkan dengan margin atas 24 px dan area garis diperbesar agar tersedia ruang tanda tangan yang lebih nyaman.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: hapus elemen tanda tangan
- Elemen garis dan teks “Waka Akademik” dihapus dari PDF detail sesuai kebutuhan distribusi laporan.
- PDF tetap portrait dan tabel/data tidak berubah.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: redaksi header laporan
- Header PDF detail disesuaikan menjadi “Rekap Kehadiran Santri Juli 2026”.
- Subjudul menjadi “Kegiatan Akademik IMTAQ Isy Karima · Semester I · Tahun Ajaran 2026/2027”.
- Ringkasan menjadi “Laporan bulanan · Kelas [nama kelas] · Jumlah santri: [jumlah]”.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: keseimbangan header
- Jarak judul dan subjudul diperlebar, line-height subjudul ditingkatkan, dan padding vertikal header disesuaikan dengan tinggi logo.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: nomor urut santri
- Menambahkan kolom “No” di sisi paling kiri PDF detail dengan nomor urut tampilan sebelum Nama Santri.
- Nomor urut tidak menggantikan atau mengubah Student ID maupun data sumber.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: jarak headline-tagline
- Jarak bawah headline PDF detail diperlebar dari 5 px menjadi 9 px agar tagline lebih terpisah dan mudah dibaca.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: istilah rekap utama
- Header tabel PDF rekap utama dirapikan dengan perataan angka dan lebar kolom yang lebih seimbang.
- Istilah teknis diganti menjadi “Dasar Hitung”, “Dikecualikan”, dan “% Kehadiran” tanpa mengubah formula atau nilai.
- Catatan laporan menjelaskan bahwa Dikecualikan tidak masuk perhitungan dan bukan berarti absen.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: urutan kelas
- Rekap laporan diurutkan berdasarkan jenjang: Kelas 1, Kelas 2A, Kelas 2B, Kelas 3A, lalu Kelas 3B.
- Urutan diterapkan pada halaman laporan serta ekspor CSV/PDF; nilai dan aturan perhitungan tidak berubah.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: istilah sesi santri
- Label PDF/CSV rekap utama “Dasar Hitung” diganti menjadi “Sesi Santri” agar lebih mudah dipahami di lingkungan pesantren.
- Label “Dikecualikan” dan “% Kehadiran” dipertahankan sebagai istilah operasional yang jelas; catatan PDF menjelaskan makna Sesi Santri.
- Nilai, denominator, formula, dan business rule tidak berubah.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: label Jumlah Sesi
- Label “Sesi Santri” disesuaikan menjadi “Jumlah Sesi” pada PDF dan CSV rekap utama.
- Keterangan catatan laporan ikut disesuaikan; nilai dan formula tetap tidak berubah.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: kesimpulan kelas
- PDF detail per kelas kini menampilkan kesimpulan persentase kehadiran kelas di bagian bawah tabel.
- Persentase dihitung dari total Hadir ÷ total Jumlah Sesi sesuai denominator laporan, bukan rata-rata persentase per santri.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: nomor rekap kelas
- Menambahkan kolom “No” di paling kiri tabel PDF rekap seluruh kelas.
- Kelas diberi nomor urut, sedangkan baris TOTAL memakai tanda “—”; data dan perhitungan tidak berubah.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: kesimpulan seluruh kelas
- Menambahkan kotak keterangan di bawah tabel PDF rekap utama: “Persentase Kehadiran Seluruh Kelas Juli 2026”.
- Nilai menggunakan total Hadir ÷ total Jumlah Sesi seluruh kelas sesuai formula laporan.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: catatan laporan
- Catatan PDF rekap utama diganti dengan redaksi yang lebih mudah dipahami pengguna.
- Catatan dipindahkan setelah keterangan persentase kehadiran seluruh kelas dan label “Catatan:” dibuat tebal.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-DASHBOARD: routing dashboard Waka
- Audit menemukan akun Waka diarahkan ke halaman administrasi `/admin/academic`, bukan dashboard operasional modern `/academic/dashboard`.
- Login kini mengarahkan role `WAKA_AKADEMIK` dan `WALI_KELAS` ke dashboard operasional, sementara akses Waka ke administrasi akademik tetap tersedia.
- Verifikasi browser menampilkan Dashboard Waka Akademik modern dengan 84 santri, filter periode, KPI, pemantauan kelas, tren, dan laporan.
- Verifikasi: 25 test lulus dengan 93 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: header rekap seluruh kelas
- Header PDF rekap utama disamakan dengan format detail: tagline “Laporan Bulanan Kegiatan Akademik”, headline “Rekap Kehadiran Seluruh Kelas Juli 2026”, dan tagline semester/tahun ajaran.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: ruang baris dan logo
- Tinggi baris tabel detail diperlonggar dengan padding 8 px agar nama santri lebih nyaman dibaca.
- Logo detail diperbesar proporsional dari 14 mm menjadi 17 mm, termasuk penyesuaian lebar kolom logo.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: redaksi rekap seluruh kelas
- PDF rekap utama memakai judul “Rekap Kehadiran Seluruh Kelas Juli 2026”.
- Subjudul menjadi “Kegiatan Akademik IMTAQ Isy Karima · Semester I · Tahun Ajaran 2026/2027”.
- Ringkasan menampilkan “Laporan bulanan lima kelas · Jumlah santri” yang dihitung dari total roster laporan.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: jarak headline rekap utama
- Jarak bawah headline PDF rekap seluruh kelas diperlebar dari 3 px menjadi 9 px agar tagline lebih longgar.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: struktur header detail
- Header PDF detail disusun menjadi tagline 1, headline nama kelas, lalu tagline 2 sesuai rancangan pengguna.
- Ringkasan diperbaiki agar menampilkan nama kelas satu kali, tanpa pengulangan “Kelas Kelas”.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-MONTHLY-DETAIL follow-up: hapus ringkasan detail
- Baris ringkasan kelas dan jumlah santri di bawah header PDF detail dihapus karena daftar sudah memiliki kolom nomor.
- Tabel kini langsung mengikuti garis pemisah header; data santri dan penomoran tetap utuh.
- Verifikasi: 8 test lulus dengan 48 assertion, Blade cache lulus, dan syntax check lulus.
# 2026-09-08 — IMP-WAKA-DASHBOARD: satu navigasi untuk Waka
- Route `/admin/academic` untuk Waka Akademik kini mengarahkan ke dashboard operasional `/academic/dashboard` yang memakai sidebar tunggal.
- Dashboard administrasi tetap dipakai Super Admin; hak akses dan subroute administrasi akademik tidak diubah.
- Verifikasi: 25 test lulus dengan 91 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: label sidebar
- Label teks sidebar dipertahankan terlihat pada layar kecil agar fungsi setiap ikon jelas: Beranda, Data Kehadiran, Pemantauan Kelas, dan Laporan.
- Sidebar dibuat responsif: tetap berbentuk kolom pada tablet dan berubah menjadi navigasi dua kolom pada ponsel.
- Verifikasi: 25 test lulus dengan 91 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: konsistensi halaman operasional
- Sidebar dashboard digunakan bersama pada halaman Perlu Perhatian Kehadiran, Sesi Kehadiran Selesai, dan detail/pengisian kehadiran.
- Navigasi lama di halaman-halaman tersebut dihapus; menu aktif, identitas peran, dan responsivitas mengikuti shell dashboard Waka.
- Verifikasi: 28 test lulus dengan 99 assertion, Blade cache lulus, dan browser menampilkan sidebar berlabel.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: sidebar ringkas saat tidak hover
- Sidebar desktop/tablet kini tampil ringkas dengan ikon saja dan melebar saat kursor atau fokus keyboard masuk untuk menampilkan label menu.
- Pada ponsel, label tetap terlihat karena perangkat sentuh tidak memiliki interaksi hover yang andal.
- Verifikasi: 28 test lulus dengan 99 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: sidebar laporan bulanan
- Halaman daftar dan detail laporan bulanan kini memakai sidebar operasional yang sama, dengan menu “Laporan” sebagai konteks aktif.
- Navigasi lama pada halaman laporan dihapus; tautan CSV, PDF, detail santri, dan pengesahan tetap dipertahankan.
- Verifikasi: 16 test lulus dengan 79 assertion, Blade cache lulus, dan browser memuat laporan bulanan dengan shell sidebar.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: hapus tombol kembali laporan
- Tombol “Kembali” di halaman daftar dan detail laporan bulanan dihapus karena sidebar menjadi navigasi utama.
- Verifikasi: 11 test lulus dengan 43 assertion, Blade cache lulus, dan browser memastikan tautan “Kembali” sudah tidak tampil.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: identitas dan keluar di sidebar
- Identitas peran aktif dan tombol “Keluar” dipindahkan dari header ke bagian bawah sidebar.
- Header halaman laporan, pemeriksaan, detail sesi, dan dashboard kini hanya memuat konteks halaman tanpa menu pengguna duplikat.
- Verifikasi: 11 test lulus dengan 43 assertion, Blade cache lulus, dan browser memastikan header laporan tidak lagi menampilkan identitas pengguna atau tombol keluar.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: sidebar mobile interaktif
- Pada layar ponsel sidebar kembali tertutup sebagai ikon saja; ketukan di sidebar membukanya untuk menampilkan ikon dan label.
- Ketukan di area luar sidebar menutup kembali panel, sementara desktop tetap menggunakan perilaku melebar saat hover atau fokus.
- Verifikasi: 11 test lulus dengan 43 assertion, Blade cache lulus, dan browser menampilkan identitas serta tombol keluar di dalam sidebar.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: logo IMTAQ pada sidebar
- Kotak teks “IM” pada brand sidebar diganti dengan aset logo IMTAQ resmi dari `public/images/logo-imtaq.png`.
- Ukuran logo dibuat proporsional untuk mode sidebar ikon dan mode sidebar terbuka, termasuk dashboard utama yang memiliki shell khusus.
- Verifikasi: 11 test lulus dengan 43 assertion dan Blade cache lulus.
# 2026-09-08 — IMP-WAKA-DASHBOARD follow-up: ringkas header dashboard
- Keterangan panjang mengenai peran dan fungsi dashboard di bawah judul dihapus sesuai rancangan tampilan.
- Verifikasi: 7 test lulus dengan 29 assertion dan Blade cache lulus.
# 2026-09-09 — IMP-WAKA-DASHBOARD: sinkronisasi snapshot Juli dan identitas kelas
- Dashboard Waka mempertahankan identitas kelas non-Pilot, lalu memetakan snapshot rekap `2026-07` berdasarkan kode kelas kanonik.
- Untuk rentang dashboard Juli 2026, KPI dan pemantauan kelas memakai angka snapshot: 84 santri, 1.198 kesempatan, dan 93,99% kehadiran; tren harian dikosongkan karena snapshot tidak menyimpan rincian per hari.
- Rentang selain Juli tetap memakai transaksi sesi/detail kehadiran seperti sebelumnya.
- Verifikasi: 20 test lulus dengan 65 assertion, Blade cache lulus, dan audit database read-only mengonfirmasi angka dashboard sama dengan rekap Juli.
# 2026-09-09 — IMP-WAKA-DASHBOARD follow-up: KPI guru aktif
- KPI Guru aktif kini mengakui status penugasan mengajar yang sah (`ACTIVE`, `APPROVED`, dan `PUBLISHED`) dengan tetap memeriksa masa berlaku dan status staf aktif.
- Verifikasi database: cakupan Juli menghasilkan 16 guru aktif; seluruh regresi dashboard lulus, 25 test dengan 91 assertion.
# 2026-09-09 — IMP-WAKA-DASHBOARD follow-up: ringkasan snapshot pada grafik
- Bagian grafik dashboard tidak lagi kosong saat memakai snapshot Juli; ditampilkan satu batang ringkasan “Juli 2026” dengan persentase 93,99%.
- Label dan keterangan diubah agar jelas bahwa data tersebut merupakan ringkasan bulanan, bukan tren harian; kontrol 7/14/30 hari hanya tampil untuk sumber transaksi harian.
- Verifikasi: 20 test lulus dengan 65 assertion, Blade cache lulus, dan database read-only mengonfirmasi satu titik ringkasan Juli.
# 2026-09-09 — IMP-WAKA-DASHBOARD follow-up: filter sesi masa depan
- Monitor “Perlu Perhatian Kehadiran” kini hanya menampilkan sesi yang sudah mulai dan berasal dari kelas non-Pilot.
- Sesi terjadwal seperti 31 Desember 2026 tidak lagi dianggap sebagai temuan kehadiran sebelum waktunya.
- Verifikasi: 21 test lulus dengan 70 assertion dan Blade cache lulus; data historis attendance tidak diubah.
# 2026-09-09 — IMP-WAKA-DASHBOARD follow-up: nama sesi mudah dibaca
- ID sesi teknis yang panjang tidak lagi ditampilkan pada monitor pengecualian.
- Nama sesi diganti menjadi “Sesi [nama kelas]”; kode sesi internal tetap dipakai sistem tetapi tidak diubah.
- Verifikasi: targeted test dan Blade cache lulus; data attendance tidak diubah.
# 2026-09-09 — IMP-WAKA-ATTENDANCE-ACTION: tautan Isi Kehadiran
- Setiap baris temuan pada monitor pengecualian kini memiliki tombol “Isi Kehadiran” yang membuka halaman pengisian sesi terkait.
- Route tujuan dan otorisasi penyimpanan tetap menggunakan workflow attendance yang sudah ada; tidak ada perubahan pada data historis.
- Verifikasi: 3 test lulus dengan 13 assertion dan Blade cache lulus.
# 2026-09-09 — IMP-WAKA-ATTENDANCE-RBAC: Waka mengisi semua kelas
- Waka Akademik dengan Staff identity tertaut kini dapat membuka, menyimpan draf, dan mengesahkan kehadiran pada semua kelas.
- Wali Kelas tetap wajib memiliki assignment wali kelas aktif untuk kelas terkait; seluruh tindakan tetap diaudit dengan identitas Staff.
- Verifikasi: 11 test lulus dengan 63 assertion, regresi UI/exception 9 test lulus dengan 57 assertion, dan Blade cache lulus.
# 2026-09-09 — IMP-ACADEMIC-GROOMING-NOTES: catatan kerapian per sesi
- Catatan kerapian santri kini disimpan pada entity terpisah per peserta dan per sesi, sehingga “Tidak berseragam” tidak memengaruhi status atau persentase kehadiran.
- Form pengisian dan tampilan pemeriksaan menampilkan kolom “Catatan kerapian”; pencatatan tetap mengikuti otorisasi attendance dan audit actor.
- Verifikasi: 11 test lulus dengan 64 assertion, Blade cache lulus, dan migration syntax check lulus.
# 2026-09-09 — IMP-ACADEMIC-DISCIPLINE-CODES: Catatan Ketertiban terstruktur
- Label diganti menjadi “Catatan Ketertiban” dengan pilihan Rapi, Tidak berseragam, Seragam tidak lengkap, Tidak membawa buku, Tidak berpeci, dan Catatan tambahan.
- Tombol “Tandai semua rapi” mengisi pilihan Rapi untuk seluruh santri; petugas dapat mengubah hanya santri yang memiliki pelanggaran dan menambahkan keterangan opsional.
- Verifikasi: 11 test lulus dengan 65 assertion, Blade cache dan migration syntax check lulus; migration additive berhasil dijalankan lokal.
# 2026-09-09 — IMP-ACADEMIC-DISCIPLINE-CODES follow-up: label tombol
- Tombol massal diganti menjadi “Tandai semua tertib” agar konsisten dengan istilah Catatan Ketertiban; perilaku tetap menetapkan pilihan Rapi.
- Verifikasi: 6 test lulus dengan 47 assertion dan Blade cache lulus.
# 2026-09-09 — IMP-ACADEMIC-SESSION-LABEL: nama sesi mudah dipahami
- ID sesi teknis tidak lagi ditampilkan pada halaman pengisian; diganti menjadi “Sesi Kehadiran Kelas [nama kelas]”.
- Kode sesi internal tetap dipertahankan untuk identifikasi sistem.
- Verifikasi: 6 test lulus dengan 49 assertion dan Blade cache lulus.
# 2026-09-09 — IMP-ACADEMIC-SIDEBAR-LOGO: logo dashboard terpusat
- Logo pada sidebar dashboard kini dipusatkan pada mode ikon dan tetap dirapikan ke kiri saat sidebar terbuka.
- Verifikasi: 18 test lulus dengan 62 assertion dan Blade cache lulus.
# 2026-09-09 — IMP-ACADEMIC-NAV-LABEL: label kontrol kehadiran
- Label menu sidebar “Data Kehadiran” diganti menjadi “Kontrol Kehadiran” pada dashboard dan halaman operasional.
- Rute, fungsi, dan otorisasi menu tetap tidak berubah.
# 2026-09-09 — IMP-ACADEMIC-SIDEBAR-ALIGNMENT: posisi logo sidebar
- Jarak logo dari sisi kiri disamakan dengan area ikon navigasi saat sidebar terbuka pada seluruh halaman akademik.
- Mode sidebar ringkas tetap mempertahankan logo terpusat; perubahan hanya memengaruhi alignment saat detail sidebar tampil.
# 2026-09-09 — IMP-ACADEMIC-SIDEBAR-OUTER-MARGIN: margin luar logo dan ikon
- Inset sisi luar logo dan ikon navigasi diseragamkan saat sidebar terbuka, termasuk tampilan mobile.
# 2026-09-09 — IMP-ACADEMIC-SIDEBAR-WIDTH: cegah teks sidebar terpotong
- Lebar sidebar saat terbuka diperbesar agar label navigasi, termasuk “Kontrol Kehadiran”, tetap berada di dalam panel.
# 2026-09-09 — IMP-ACADEMIC-SIDEBAR-ICON-SIZE: ikon ringkas lebih terbaca
- Ukuran dan lebar area ikon sidebar ringkas diperbesar secara proporsional agar ikon tetap jelas saat sidebar belum dibuka.
# 2026-09-09 — IMP-ACADEMIC-DASHBOARD-PERIOD-FILTER: status dan non-pilot konsisten
- Kartu status kehadiran dashboard kini menghitung sesi dalam periode yang dipilih, bukan selalu tanggal hari ini.
- Filter non-pilot diseragamkan pada laporan bulanan, detail/export laporan, publikasi, dan daftar review sesi.
- Verifikasi: 156 test akademik lulus dengan 558 assertion, PHP lint lulus, dan Blade cache lulus.
# 2026-09-09 — IMP-ACADEMIC-DASHBOARD-QUICK-RANGES: preset rentang waktu
- Dashboard menyediakan preset 7 hari terakhir, 1 bulan terakhir, 2 bulan terakhir, dan 3 bulan terakhir.
- Rentang preset dihitung sampai hari ini; tanggal manual tetap tersedia bila preset tidak dipilih.
- Verifikasi: 19 test dashboard lulus dengan 65 assertion dan Blade cache lulus.
# 2026-09-09 — IMP-ACADEMIC-DASHBOARD-YEAR-MONTH: pilihan bulan tahun ajaran
- Preset rentang cepat diganti menjadi pilihan bulan berdasarkan tahun ajaran aktif non-pilot.
- Pemilihan bulan otomatis menggunakan tanggal awal hingga akhir bulan tersebut dan menolak bulan di luar tahun ajaran aktif.
- Verifikasi: 19 test dashboard lulus dengan 65 assertion dan Blade cache lulus.
# 2026-09-09 — FIX-ACADEMIC-DASHBOARD-SNAPSHOT-STATUS: status Juli selaras
- Periode yang memakai snapshot rekap Juli kini menampilkan kelengkapan data snapshot, bukan status pengesahan transaksi sesi langsung.
- Label dan keterangan kartu disesuaikan agar angka 100% snapshot tidak bercampur dengan status sesi live yang belum disahkan.
- Verifikasi: 157 test akademik lulus dengan 561 assertion dan Blade cache lulus.
# 2026-09-09 — FIX-ACADEMIC-DASHBOARD-RANGE-AUTH: akses preset historis
- Pemeriksaan role dashboard menggunakan akhir periode terpilih agar preset multi-bulan tidak menghasilkan 403 hanya karena tanggal mulai lebih awal dari masa aktif role.
# 2026-09-09 — IMP-SCHEDULE-UI-PHASE1: shell dan list view jadwal akademik
- Halaman daftar jadwal memakai shell akademik bersama, logo IMTAQ, sidebar konsisten, header ringkas, kartu ringkasan runtime, filter readable, dan switcher Daftar/Mingguan.
- Daftar menampilkan data jadwal existing dengan identitas non-pilot, label hari/pola yang mudah dibaca, serta mempertahankan URL detail/edit yang sudah ada.
- Mode Mingguan masih berupa placeholder terarah untuk Phase 2; tidak ada grid waktu, migrasi, seed, import, RBAC, aturan bisnis, atau perubahan data jadwal.
- Verifikasi: 157 test akademik lulus dengan 561 assertion, view cache lulus, dan smoke test browser untuk daftar jadwal lulus.
# 2026-09-09 — IMP-SCHEDULE-UI-PHASE2: grid mingguan berbasis waktu aktual
- Mode Mingguan kini memakai seluruh data jadwal existing yang telah difilter, dengan kolom Sabtu, Ahad, Senin, Selasa, Rabu, dan Kamis; Jumat hanya diberi keterangan libur.
- Posisi kartu dihitung dari start_time aktual dan tinggi kartu dari durasi end_time-start_time, termasuk quarter-hour seperti 10:15.
- Kartu menampilkan mapel, kelas, guru dari relasi existing dan membuka rute detail/edit yang sudah ada.
- Tidak ada perubahan database, seed, import, RBAC, aturan bisnis, conflict engine, atau data jadwal.
- Verifikasi: 7 test schedule admin lulus dengan 27 assertion, 157 test akademik lulus dengan 561 assertion, view cache/lint lulus, dan smoke test browser grid mingguan lulus.
# 2026-09-09 — FIX-SCHEDULE-CREATE-NONPILOT: pilihan kelas resmi
- Formulir Tambah Jadwal kini hanya memuat penugasan mengajar dari tahun ajaran non-pilot, sehingga data kelas resmi dapat dipilih dan data pilot tidak tercampur.
- Endpoint penyimpanan juga menolak penugasan pilot agar pembatasan tidak hanya bergantung pada tampilan formulir.
- Verifikasi: 8 test schedule admin lulus dengan 31 assertion, view cache lulus, PHP lint lulus, dan smoke test formulir lulus.
# 2026-09-09 — FIX-SCHEDULE-CREATE-PUBLISHED: penugasan terbit tersedia
- Status penugasan VALIDATED, APPROVED, dan PUBLISHED kini ikut tersedia pada formulir Tambah Jadwal; penyaringan non-pilot tetap berlaku.
- Verifikasi: 9 test schedule admin lulus dengan 33 assertion, view cache/lint lulus, dan smoke test menampilkan penugasan resmi Kelas 1–3A/3B.
# 2026-09-09 — FIX-SCHEDULE-CREATE-ASSIGNMENT-LABEL: identitas penugasan lebih jelas
- Dropdown penugasan kini menampilkan guru/asatidzah selain kode, kelas, dan mata pelajaran agar dua penugasan dengan kelas-mapel sama tidak membingungkan.
- Verifikasi: 9 test schedule admin lulus dengan 34 assertion, view cache/lint lulus.
# 2026-09-09 — IMP-ACADEMIC-SESSION-SUBSTITUTION-UI: penggantian guru per sesi
- Halaman Kehadiran Santri kini menyediakan form Waka untuk memilih guru pengganti dan mencatat alasan pada sesi tertentu.
- Perubahan memakai SubstitutionService existing, menyimpan audit ScheduleChange dan SessionTeacherParticipation, serta menjaga guru utama dan jadwal reguler.
- Verifikasi: 7 test StudentAttendanceUi lulus dengan 53 assertion, 3 test SubstitutionService lulus dengan 13 assertion, view cache dan PHP lint lulus.
# 2026-09-09 — FIX-ACADEMIC-SUBSTITUTION-FORM-LAYOUT: form penggantian rapi
- Field guru pengganti, alasan, dan tombol disusun vertikal dengan lebar responsif agar label tidak bertabrakan pada desktop maupun layar kecil.
- Verifikasi: 7 test StudentAttendanceUi lulus dengan 53 assertion, view cache dan PHP lint lulus.
# 2026-09-09 — IMP-ACADEMIC-SESSION-CANCELLATION: perluasan pembatalan sesi
- Sesi PLANNED dapat dibatalkan pada hari yang sama atau setelah waktunya lewat bila belum memiliki data kehadiran; sesi COMPLETED/CANCELLED/RESCHEDULED atau sesi yang sudah memiliki isian tetap dilindungi.
- Halaman Kehadiran Santri menampilkan formulir Batalkan Sesi dengan alasan wajib, mencatat audit ScheduleChange, dan menyembunyikan kontrol pengisian setelah sesi dibatalkan.
- Verifikasi: 11 test lulus dengan 60 assertion, Blade cache lulus, dan PHP lint controller/service lulus.
# 2026-09-09 — IMP-ACADEMIC-ATTENDANCE-PAGE-POLISH: rapikan halaman kehadiran santri
- Menghapus header judul yang berulang, menyatukan hierarki kartu sesi/ringkasan, memperbaiki jarak form tindakan, dan memperjelas petunjuk pengisian.
- Tabel status, catatan kehadiran, catatan ketertiban, dan status pengisian diberi lebar minimum yang konsisten agar tetap terbaca dan dapat digeser pada layar kecil.
- Verifikasi: 7 test UI lulus dengan 53 assertion dan Blade cache lulus.
# 2026-09-09 — FIX-ACADEMIC-REPLACEMENT-TEACHER-OPTIONS: bersihkan guru pilot
- Opsi guru pengganti pada halaman kehadiran kini mengecualikan seluruh staff berkode `PILOT-*`, termasuk Azhar dan Guru Sample 3A/3B.
- Data master tidak dihapus; hanya tidak ditawarkan untuk transaksi penggantian guru agar histori tetap aman.
- Verifikasi: test UI kehadiran dan Blade cache lulus.
# 2026-09-09 — FIX-ADMIN-SCHEDULE-PILOT-OPTIONS: bersihkan guru pilot dari jadwal
- Halaman Jadwal Akademik kini mengecualikan staff berkode `PILOT-*` dari dropdown filter guru dan dari data operasional daftar/mingguan.
- Data pilot tetap dipertahankan di database sebagai histori dan tidak dihapus.
- Verifikasi: test admin jadwal dan Blade cache lulus.
# 2026-09-09 — IMP-ACADEMIC-SIDEBAR-CONSOLIDATION: satukan sidebar dashboard
- Dashboard Waka kini memakai partial sidebar akademik bersama; logo, menu role-aware, interaksi hover/mobile, identitas, dan tombol keluar tidak lagi memiliki implementasi terpisah.
- Perubahan hanya pada template navigasi, tanpa mengubah otorisasi, data, atau transaksi bisnis.
- Verifikasi: 19 test Dashboard Waka lulus dengan 65 assertion, Blade cache dan PHP lint lulus.
# 2026-09-09 — CORR-IMPORT-CLASS1-TUESDAY: selaraskan jadwal Selasa dengan Senin
- Koreksi database lokal untuk Kelas 1 periode 1 Juli–31 Desember 2026: Selasa 07:00–09:30 menjadi 08:00–09:30 dan Selasa 10:15–11:30 menjadi 10:00–11:00.
- 52 sesi yang semuanya PLANNED dan belum memiliki data kehadiran ikut diperbarui; setiap koreksi dicatat sebagai IMPORT_CORRECTION dan tidak ada data kehadiran yang dihapus.
- Verifikasi: 19 test terkait jadwal/kehadiran lulus dengan 100 assertion, view cache dan PHP lint lulus, serta query verifikasi menunjukkan pasangan Senin/Selasa sudah sama.
# 2026-09-10 — IMP-ACADEMIC-WAKA-BULK-CANCEL: pembatalan sesi massal
- Waka Akademik/Super Admin dapat memilih kelas resmi dan rentang tanggal, mempratinjau jumlah sesi PLANNED yang aman dibatalkan, lalu mengisi alasan pembatalan satu kali.
- Sesi yang sudah memiliki kehadiran atau berubah status dilewati; sesi tidak dihapus dan setiap pembatalan dicatat sebagai ScheduleChange audit.
- Verifikasi: 8 test lulus dengan 26 assertion, Blade cache/lint lulus, dan route GET preview serta POST bulk-cancel terdaftar.
# 2026-09-10 — FIX-ACADEMIC-BULK-CANCEL-FORM-SPACING: rapikan blok pembatalan massal
- Jarak antara catatan alasan dan tombol pembatalan diperjelas dengan layout form bertingkat agar kedua kontrol tidak menempel.
- Verifikasi: 4 test UI lulus dengan 19 assertion dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-EXCEPTION-LIST-FILTER: fokus daftar temuan
- Daftar temuan kini memiliki filter bulan, kelas non-pilot, dan urutan terbaru/terlama agar pengisian atau pengeditan dapat dilakukan per kelompok.
- Filter daftar dipisahkan dari filter pratinjau pembatalan massal dan tetap memakai endpoint GET yang sama.
- Verifikasi: 4 test UI lulus dengan 22 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-DATE-DATEPICKER-LOCALE: tanggal lokal dengan kalender
- Nilai tanggal yang terlihat pada pembatalan sesi massal selalu `dd/mm/yyyy`, sedangkan lapisan input kalender native tetap tersedia untuk pemilihan tanggal.
- Nilai ISO internal tetap dikirim ke backend untuk menjaga validasi dan filter tanggal.
- Verifikasi: 4 test UI lulus dengan 19 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-DATE-LOCALE: format tanggal Indonesia
- Input rentang tanggal pada pembatalan sesi massal kini tampil dan menerima format `dd/mm/yyyy`, lalu dinormalisasi aman ke `Y-m-d` untuk query dan validasi.
- Verifikasi: 4 test UI lulus dengan 19 assertion, Blade cache, dan PHP lint lulus.
# 2026-09-10 — FIX-ACADEMIC-DATE-STANDARDIZATION: standar tanggal sistem
- Kontrol tanggal pada dashboard, review kehadiran, jadwal, dan master staf diberi konteks locale Indonesia (`lang="id"`); kontrol pembatalan massal menampilkan serta menerima `dd/mm/yyyy`.
- Format internal `Y-m-d` tetap dipertahankan untuk query, validasi, dan penyimpanan agar kompatibel dengan backend.
- Verifikasi: Blade cache lulus dan test `AttendanceExceptionUiTest` lulus 4 test dengan 19 assertion.
# 2026-09-10 — IMP-ACADEMIC-BULK-CANCEL-WEEKDAY: tampilkan hari pada rentang tanggal
- Form pembatalan massal kini menampilkan nama hari Indonesia untuk tanggal Mulai dan Sampai, sehingga rentang seperti Rabu 01/07/2026–Senin 06/07/2026 mudah diverifikasi.
- Verifikasi: 4 test UI lulus dengan 19 assertion dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-SCHEDULE-REVISION: revisi jadwal mulai tanggal efektif
- Halaman Edit Jadwal kini menyediakan mode revisi untuk aturan yang sudah memiliki sesi. Admin dapat menetapkan aturan baru mulai tanggal tertentu, misalnya 28 Juli 2026.
- Aturan lama ditutup sehari sebelum tanggal revisi; sesi historis dengan kehadiran dipertahankan, sesi PLANNED tanpa kehadiran setelah tanggal revisi dibatalkan dengan audit, dan sesi baru dibuat tanpa duplikasi waktu aktif.
- Mode revisi juga dapat memilih guru baru; sistem membuat teaching assignment baru sehingga guru pada histori tidak berubah.
- Verifikasi: ScheduleRuleAdminTest 12 test/44 assertion, Academic suite 161 test/579 assertion, dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-REVISION-SPACING: rapikan alasan revisi
- Field Alasan revisi pada halaman Revisi Jadwal kini tersusun vertikal, menggunakan lebar penuh, dan tidak lagi menempel/berdampingan dengan kontrol lain.
- Verifikasi: ScheduleRuleAdminTest 12 test/44 assertion dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-SCHEDULE-REVISION-SUBJECT: tambahkan mapel revisi
- Mode Revisi Jadwal kini menyediakan pilihan Mata pelajaran revisi selain guru pengajar revisi.
- Perubahan mapel membuat teaching assignment baru untuk tanggal efektif; mapel pada sesi historis tidak diubah.
- Verifikasi: ScheduleRuleAdminTest 12 test/44 assertion, Academic suite 161 test/579 assertion, dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-SCHEDULE-SAFE-DELETE: hapus jadwal tanpa histori
- Waka Akademik/Super Admin kini dapat menghapus aturan jadwal yang belum memiliki sesi melalui konfirmasi.
- Aturan yang sudah memiliki sesi tidak dapat dihapus dan UI menampilkan “Histori terkunci”; gunakan revisi atau pembatalan sesi yang aman.
- Verifikasi: ScheduleRuleAdminTest 14 test/50 assertion, Blade cache, dan PHP lint lulus.
# 2026-09-10 — IMP-ACADEMIC-SCHEDULE-ARCHIVE: arsipkan jadwal dan batalkan sesi kosong
- Halaman Revisi Jadwal kini menyediakan aksi Arsipkan & batalkan sesi kosong untuk jadwal yang sudah memiliki sesi.
- Aturan menjadi ARCHIVED; sesi PLANNED/CONFIRMED tanpa kehadiran dibatalkan dan diaudit, sedangkan sesi berkehadiran tetap dipertahankan.
- Verifikasi: ScheduleRuleAdminTest 15 test/55 assertion, Blade cache, dan PHP lint lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-ARCHIVE-SPACING: rapikan blok arsip
- Blok Alasan arsip pada halaman Revisi Jadwal kini memakai susunan vertikal, textarea penuh, dan jarak tombol yang konsisten.
- Verifikasi: ScheduleRuleAdminTest 15 test/55 assertion dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-SCHEDULE-ARCHIVE-LIST: pisahkan daftar arsip
- Daftar jadwal utama kini mengecualikan aturan berstatus ARCHIVED.
- Link “Daftar jadwal archived” ditambahkan untuk membuka filter arsip secara terpisah, dengan link kembali ke jadwal aktif.
- Verifikasi: ScheduleRuleAdminTest 15 test/55 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-ARCHIVE-LINK-POSITION: pindahkan link histori
- Link daftar jadwal archived dipindahkan ke bagian paling bawah halaman agar daftar jadwal aktif tetap menjadi fokus utama.
- Verifikasi: ScheduleRuleAdminTest 15 test/55 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-ARCHIVE-LINK-DESIGN: desain kartu histori
- Link arsip di bagian bawah kini ditampilkan sebagai kartu histori dengan latar lembut, border putus-putus, label konteks, dan tautan yang lebih jelas.
- Verifikasi: ScheduleRuleAdminTest 15 test/55 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-REVISION-SAME-DATE: revisi ulang tanggal efektif
- Revisi jadwal kini dapat menggantikan aturan yang sudah mulai pada tanggal efektif yang sama; aturan sebelumnya diarsipkan dan histori sesi tetap dipertahankan.
- Verifikasi: ScheduleRuleAdminTest 16 test/59 assertion, Academic suite 161 test/579 assertion, dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-REVISION-SUBJECT-ONLY: simpan perubahan mapel
- Koreksi bug ketika hanya mata pelajaran yang diubah: sistem kini membuat teaching assignment baru meskipun guru tetap sama.
- Verifikasi: ScheduleRuleAdminTest 17 test/62 assertion, Academic suite 161 test/579 assertion, dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-REVISION-DATE-LOCALE: format tanggal revisi
- Input tanggal pada halaman Revisi Jadwal kini tampil dan menerima format Indonesia `DD/MM/YYYY`; backend menormalisasi ke `Y-m-d` sebelum validasi.
- Verifikasi: ScheduleRuleAdminTest 17 test/62 assertion, Academic suite 161 test/579 assertion, Blade cache, dan PHP lint lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-CREATE-ACTION: tempatkan tombol tambah
- Tombol tambah jadwal dipindahkan dari header ke area tab Daftar/Mingguan agar berada dekat konteks pengelolaan daftar.
- Verifikasi: ScheduleRuleAdminTest 17 test/62 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-CREATE-CONTRAST: perbaiki kontras tombol tambah
- Teks tombol + Tambah jadwal dikembalikan menjadi putih agar tidak tertimpa style tautan tab yang membuatnya terlalu gelap.
- Verifikasi: ScheduleRuleAdminTest 17 test/62 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-CREATE-OPTION-LABEL: ringkas pilihan penugasan
- Opsi Penugasan mengajar pada form Tambah Jadwal kini menampilkan Kelas · Mapel · Guru agar lebih ringkas; kode penugasan tetap tersedia sebagai tooltip untuk identifikasi.
- Verifikasi: ScheduleRuleAdminTest 17 test/62 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-CREATE-ARCHIVED-OPTIONS: sembunyikan penugasan terarsip
- Opsi penugasan yang seluruh aturan jadwalnya sudah ARCHIVED kini tidak ditampilkan pada form Tambah Jadwal; penugasan dengan aturan aktif tetap tersedia.
- Verifikasi: ScheduleRuleAdminTest 18 test/64 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-CREATE-DUPLICATE-OPTIONS: rapikan opsi ganda
- Opsi Tambah Jadwal kini dideduplikasi berdasarkan kelas, mata pelajaran, dan guru; record penugasan terbaru diprioritaskan tanpa menghapus data sumber.
- Verifikasi: ScheduleRuleAdminTest 19 test/68 assertion dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-TEACHING-ASSIGNMENT-CREATE: tambah penugasan mengajar
- Form Tambah Jadwal kini menyediakan tautan ke menu Tambah Penugasan Mengajar.
- Waka Akademik/Super Admin dapat membuat kombinasi semester, kelas, mapel, dan guru baru dengan periode berlaku; penugasan dibuat APPROVED dan langsung tersedia untuk pemilihan jadwal.
- Validasi membatasi kelas, mapel, guru, dan tahun ajaran resmi; data pilot ditolak dan data lama tidak diubah.
- Verifikasi: ScheduleRuleAdminTest 20 test/73 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-CREATE-ASSIGNMENT-HELP: rapikan bantuan penugasan
- Bantuan Tambah Penugasan Mengajar ditempatkan di bawah field penugasan dalam kolom yang sama, sehingga tidak bertabrakan dengan field Hari pada layar dua kolom.
- Verifikasi: ScheduleRuleAdminTest 20 test/73 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-EXCEPTIONS-WEEKDAY: tampilkan hari sesi
- Kolom Waktu pada daftar temuan kehadiran kini menampilkan nama hari di atas tanggal dan jam agar pengisian lebih mudah dikonsentrasikan.
- Verifikasi: AttendanceExceptionUiTest 4 test/23 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-EXCEPTIONS-EMPTY-ROSTER: tandai sesi tanpa roster
- Sesi akademik aktif tanpa peserta wajib kini dianggap belum lengkap dan muncul sebagai “Roster santri belum dibuat” pada daftar Perlu Perhatian.
- Temuan ini juga dibawa ke payload alert kualitas data tanpa membuat data kehadiran atau status tidak hadir secara otomatis.
- Verifikasi: StudentAttendanceCompletenessCheckerTest 3 test/9 assertion, AttendanceExceptionUiTest 5 test/25 assertion, AcademicDataQualityAlertServiceTest 5 test/13 assertion, dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-WORKFLOW-SIMPLIFICATION: sederhanakan persiapan akademik
- Menu Mata Pelajaran kini tersedia dengan tambah, edit nama/status, dan akses yang sama untuk Waka Akademik/Super Admin.
- Form Tambah Jadwal kini menyediakan shortcut persiapan data mapel, guru, dan penugasan.
- Daftar Perlu Perhatian menyediakan aksi langsung Buat roster untuk sesi tanpa snapshot peserta.
- Verifikasi: ScheduleRuleAdminTest 21 test/78 assertion, AttendanceExceptionUiTest 6 test/28 assertion, StudentAttendanceCompletenessCheckerTest 3 test/9 assertion, full Academic suite 164 test/588 assertion, dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-SESSION-ROSTER-SNAPSHOT: tambah aksi snapshot roster
- Halaman detail sesi kini menyediakan tombol Waka Akademik/Super Admin untuk membuat snapshot peserta berdasarkan enrollment aktif pada tanggal sesi.
- Snapshot bersifat idempoten, tidak membuat data kehadiran, dan hanya tersedia untuk sesi PLANNED yang dapat dikelola Waka.
- Verifikasi: AttendanceExceptionUiTest 6 test/28 assertion, StudentAttendanceCompletenessCheckerTest 3 test/9 assertion, dan Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-WORKFLOW-SIMPLIFICATION-2: sederhanakan input jadwal dan roster massal
- Form Tambah Jadwal kini memprioritaskan pilihan Semester, Kelas, Mata pelajaran, dan Guru/Asatidzah; sistem otomatis memakai atau membuat penugasan yang sesuai. Penugasan tersimpan tetap tersedia sebagai opsi lanjutan untuk kompatibilitas.
- Daftar temuan kini menyediakan aksi Waka/Super Admin untuk membuat roster beberapa sesi kosong sekaligus; hanya sesi PLANNED resmi tanpa peserta yang diproses dan snapshot tetap idempoten.
- Verifikasi: ScheduleRuleAdminTest 22 test/80 assertion, AttendanceExceptionUiTest 7 test/32 assertion, full Academic suite 165 test/592 assertion, dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-WORKFLOW-PILOT-FILTER: sembunyikan semester pilot
- Semester pada form Tambah Jadwal dan Tambah Penugasan Mengajar kini dibatasi ke tahun ajaran resmi; semester dengan kode tahun ajaran berakhiran `-PILOT` tidak lagi tampil atau dapat dipakai melalui alur persiapan akademik.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SEMESTER-CATALOG: lengkapi semester resmi
- Seeder struktur akademik resmi kini membuat Semester 2 tahun ajaran 2026/2027 (`S2-2026-2027`) untuk periode 1 Januari–30 Juni 2027.
- Seeder menggunakan `updateOrCreate`, sehingga aman dijalankan ulang tanpa membuat semester ganda.
# 2026-09-10 — FIX-ACADEMIC-SEMESTER-OPTION-LABEL: rapikan urutan dan label semester
- Pilihan semester pada Tambah Jadwal dan Tambah Penugasan Mengajar kini berurutan Semester I lalu Semester II.
- Label teknis diganti menjadi format ringkas `Semester I 2026/2027` dan `Semester II 2026/2027`.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-CREATE-SIDEBAR: samakan navigasi form
- Halaman Tambah Jadwal kini menggunakan layout akademik bersama dengan sidebar, menu aktif Jadwal, dan responsivitas yang sama seperti halaman daftar jadwal.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion, Blade cache, dan PHP lint lulus.
# 2026-09-10 — FIX-ACADEMIC-PREPARATION-FORMS-SIDEBAR: seragamkan form persiapan
- Halaman Tambah Penugasan Mengajar dan Tambah Mata Pelajaran kini memakai layout sidebar akademik bersama, sehingga alur persiapan data memiliki navigasi yang konsisten.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion, Blade cache, dan PHP lint lulus.
# 2026-09-10 — FIX-ACADEMIC-SIDEBAR-MASTER-NAV: tambah akses data dasar
- Sidebar akademik kini menyediakan tautan langsung ke Mata Pelajaran dan Guru/Staf bagi Waka Akademik/Super Admin, sehingga persiapan penugasan tidak perlu dilakukan melalui tautan tersembunyi di form.
- Verifikasi: ScheduleRuleAdminTest dan StaffAdminTest 27 test/103 assertion, serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SUBJECT-LIST-SIDEBAR: samakan daftar mata pelajaran
- Halaman daftar Mata Pelajaran kini memakai sidebar akademik bersama, menu Mata Pelajaran aktif, dan struktur konten yang konsisten dengan form persiapan.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STAFF-PAGES-SIDEBAR: samakan halaman guru/staf
- Halaman daftar, tambah, dan edit Guru/Staf kini memakai sidebar akademik bersama dan menu Guru/Staf aktif pada daftar/form terkait.
- Verifikasi: ScheduleRuleAdminTest dan StaffAdminTest 27 test/103 assertion, serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-CLASS-LIST-SIDEBAR: samakan daftar kelas
- Daftar Kelas kini diarahkan ke tampilan sidebar akademik bersama dengan menu Kelas aktif, tombol tambah, status tahun ajaran, dan tabel kelas tetap dipertahankan.
- Verifikasi: AcademicClassAdminTest dan StaffAdminTest 7 test/27 assertion, serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SUBJECT-EDIT-SIDEBAR: samakan edit mata pelajaran
- Halaman Edit Mata Pelajaran kini memakai sidebar akademik bersama dengan menu Mata Pelajaran aktif, tanpa mengubah aturan kode mapel terkunci dan status histori.
- Verifikasi: ScheduleRuleAdminTest, StaffAdminTest, dan AcademicClassAdminTest 30 test/114 assertion, serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SUBJECT-CREATE-FORM: rapikan form tambah mapel
- Form Tambah Mata Pelajaran kini memakai kartu yang lebih proporsional, bantuan singkat untuk kode/nama, placeholder yang jelas, serta area aksi Simpan/Batal yang terpisah.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion, Blade cache, dan PHP lint lulus.
# 2026-09-10 — FIX-ACADEMIC-STAFF-WORKFLOW-CTA: perjelas langkah dari guru ke jadwal
- Daftar Guru/Staf kini menyediakan panel Langkah berikutnya dengan akses langsung ke Tambah Penugasan dan Jadwal.
- Verifikasi: ScheduleRuleAdminTest dan StaffAdminTest 27 test/103 assertion, serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-WORKFLOW-CTA: perjelas persiapan jadwal
- Daftar Jadwal kini menyediakan panel Siapkan jadwal dengan akses langsung ke Tambah mapel, Tambah guru, dan Tambah penugasan.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-SEMESTER-FILTER: pisahkan tampilan per semester
- Daftar Jadwal kini menyediakan filter Semester resmi dengan urutan Semester I lalu Semester II, sehingga jadwal antarsemester tidak tercampur.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-MOBILE: responsifkan daftar jadwal
- Breakpoint daftar Jadwal diperluas untuk layar tablet/mobile dengan sidebar; toolbar filter dan tombol persiapan kini turun ke baris yang aman tanpa melebar keluar viewport.
- Ringkasan jadwal ikut berubah menjadi satu/dua kolom sesuai lebar layar.
- Verifikasi: ScheduleRuleAdminTest 23 test/87 assertion dan Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STUDENT-LIST-SIDEBAR: samakan daftar santri
- Daftar Santri kini memakai sidebar akademik bersama dengan menu Santri aktif.
- Pencarian, filter kelas, pagination, pengisian NIS/NISN, dan akses edit tetap dipertahankan.
- Verifikasi: StudentAdminTest dan StaffAdminTest 8 test/31 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STUDENT-FORMS-SIDEBAR: seragamkan form santri
- Halaman Tambah Santri dan Edit Santri kini memakai sidebar akademik bersama dengan menu Santri aktif.
- Form dibuat responsif dan tetap mempertahankan validasi, penetapan kelas, serta histori perubahan enrollment.
- Verifikasi: StudentAdminTest, StaffAdminTest, dan ScheduleRuleAdminTest 31 test/118 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-CLASS-PAGES-SIDEBAR: seragamkan halaman kelas
- Daftar, Tambah, dan Edit Kelas kini memakai sidebar akademik bersama dengan menu Kelas aktif.
- Layout form responsif dan tetap mempertahankan penguncian identitas kelas serta histori data.
- Verifikasi: AcademicClassAdminTest, StudentAdminTest, dan StaffAdminTest 11 test/42 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SCHEDULE-EDIT-SIDEBAR: seragamkan revisi jadwal
- Halaman Edit/Revisi Jadwal kini memakai sidebar akademik bersama dengan menu Jadwal aktif.
- Revisi guru/mapel, tanggal format dd/mm/yyyy, arsip sesi kosong, dan hapus jadwal tanpa sesi tetap tersedia sesuai aturan histori.
- Verifikasi: ScheduleRuleAdminTest, AcademicClassAdminTest, dan StudentAdminTest 30 test/113 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STRUCTURE-PAGES-SIDEBAR: seragamkan struktur akademik
- Halaman Tingkat & Wali Kelas dan Edit Wali Kelas kini memakai sidebar akademik bersama dengan menu Struktur aktif.
- Form penetapan dan perubahan wali kelas tetap mempertahankan alur histori serta dibuat responsif.
- Verifikasi: AcademicStructureAdminTest, AcademicClassAdminTest, StudentAdminTest, dan StaffAdminTest 16 test/62 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STRUCTURE-FORM-LAYOUT: rapikan penetapan wali kelas
- Area Tambah Wali Kelas menggunakan grid dua kolom yang tidak meluber dan turun menjadi satu kolom pada mobile.
- Tabel wali kelas tetap dapat digeser secara horizontal pada layar kecil.
- Verifikasi: AcademicStructureAdminTest, AcademicClassAdminTest, StudentAdminTest, dan StaffAdminTest 16 test/62 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STRUCTURE-FORM-SPACING: beri jarak blok aksi
- Tombol aksi pada form Tambah Tingkat dan Tambah Wali Kelas diberi jarak yang konsisten dari field terakhir agar blok tidak tampak menempel.
- Verifikasi: AcademicStructureAdminTest dan AcademicClassAdminTest 8 test/31 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STRUCTURE-DATE-FORMAT: seragamkan format tanggal
- Input tanggal penetapan Wali Kelas pada halaman Struktur menampilkan format Indonesia `dd/mm/yyyy`.
- Backend menormalkan input tersebut ke format database tanpa mengubah histori penetapan.
- Verifikasi: AcademicStructureAdminTest, AcademicClassAdminTest, dan StudentAdminTest 13 test/49 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-ROSTER-ACTION: rapikan aksi roster massal
- Tombol Buat roster untuk sesi kosong kini berada dalam panel aksi yang terpisah dari petunjuk tabel dan tidak lagi tampil sebagai bar penuh.
- Aksi tetap dibatasi pada sesi kosong yang terdeteksi dan tidak mengubah fakta kehadiran.
- Verifikasi: AttendanceExceptionUiTest, StudentAttendanceCompletenessCheckerTest, dan ScheduleRuleAdminTest 33 test/128 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-ROSTER-BUTTON: modernisasi tombol roster
- Tombol Buat roster diberi gaya aksi modern dengan ikon, warna bertingkat, radius, shadow, dan state hover/focus yang jelas.
- Verifikasi: AttendanceExceptionUiTest dan StudentAttendanceCompletenessCheckerTest 10 test/41 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-DASHBOARD-MOBILE-KPI: rapikan KPI mobile
- Kartu KPI Dashboard kini tersusun satu kolom pada layar mobile kecil agar label, angka, dan keterangan tidak berhimpitan.
- Verifikasi: AdminDashboardTest, AcademicRoleDashboardServiceTest, StudentAdminTest, dan ScheduleRuleAdminTest 48 test/170 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-DASHBOARD-MOBILE-FILTER: rapikan filter periode
- Keterangan rentang periode pada filter Dashboard kini mengambil satu baris penuh di mobile agar tidak bertabrakan dengan field dan tombol.
- Verifikasi: AdminDashboardTest, AcademicRoleDashboardServiceTest, dan StudentAdminTest 25 test/83 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-ACTIONS: modernisasi aksi kehadiran
- Tombol aksi pada halaman Isi Kehadiran Santri kini memakai gaya modern yang konsisten dengan aksi roster, termasuk state hover/focus.
- Tombol sekunder tetap dibedakan secara visual tanpa mengubah proses pengisian atau pengesahan.
- Verifikasi: StudentAttendanceUiTest, AttendanceExceptionUiTest, dan AdminDashboardTest 16 test/91 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-DASHBOARD-CLASS-CARDS: rapikan pemantauan kelas
- Kartu Pemantauan Kelas mendapat hover yang lebih halus dan metadata kelengkapan diratakan kiri pada mobile agar mudah dipindai.
- Progress bar tetap menjadi indikator utama tanpa mengubah angka atau sumber data.
- Verifikasi: AdminDashboardTest dan AcademicRoleDashboardServiceTest 21 test/68 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-CLASS-TABLE-ACTIONS: perjelas tabel kelas
- Status dan tombol Edit pada Daftar Kelas kini memiliki penekanan visual yang lebih jelas dan tetap aman saat tabel digeser di mobile.
- Verifikasi: AcademicClassAdminTest, AcademicStructureAdminTest, dan StudentAdminTest 13 test/49 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-SUBJECT-TABLE-ACTIONS: perjelas tabel mapel
- Daftar Mata Pelajaran kini memakai tabel dengan status dan tombol Edit yang lebih mudah dipindai.
- Penjelasan status Tidak aktif ditambahkan untuk mencegah mapel histori dipakai pada pilihan baru.
- Verifikasi: ScheduleRuleAdminTest, StudentAdminTest, dan StaffAdminTest 31 test/118 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STAFF-TABLE-ACTIONS: perjelas tabel guru
- Status dan tombol Edit pada Master Guru/Staf kini memiliki penekanan visual yang konsisten dengan tabel master lainnya.
- Verifikasi: StaffAdminTest dan AcademicClassAdminTest 7 test/27 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-STUDENT-ROSTER-SCOPE: tampilkan roster resmi
- Daftar Santri tidak lagi membatasi enrollment pada dua alasan hard-code; daftar kini memakai enrollment aktif pada kelas resmi di tahun ajaran aktif.
- Enrollment hasil rekonsiliasi resmi tetap tampil, sedangkan kelas dan data pilot tetap tidak ikut tercampur.
- Verifikasi: StudentAdminTest 5 test/18 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-SHOW-RESPONSIVE: responsifkan pengisian kehadiran
- Metadata sesi, petunjuk, dan catatan panjang kini membungkus aman agar tidak meluber pada viewport tablet/mobile.
- Ringkasan dan form penggantian guru mendapat batas lebar yang aman; tabel peserta tetap dapat digeser horizontal untuk kolom input lengkap.
- Verifikasi: StudentAttendanceUiTest dan AttendanceExceptionUiTest 14 test/88 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-FINALIZE-FEEDBACK: tangani pengesahan belum lengkap
- Pengesahan kehadiran yang belum lengkap kini kembali ke halaman input dengan pesan berbahasa Indonesia, bukan halaman 500.
- Tombol pengesahan memeriksa status kosong di browser dan disembunyikan saat roster belum dibuat; validasi backend tetap menjadi pengaman utama.
- Verifikasi: StudentAttendanceUiTest dan StudentAttendanceFinalizerTest 14 test/77 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-FINALIZE-SUBMIT: simpan pilihan sebelum sahkan
- Tombol Sahkan kehadiran kini berada pada form input yang sama, sehingga pilihan status yang sedang diisi ikut terkirim.
- Backend menyimpan status dan catatan yang dikirim sebelum menjalankan finalizer; pengesahan langsung tanpa data tetap ditolak dengan feedback yang aman.
- Verifikasi: StudentAttendanceUiTest dan StudentAttendanceFinalizerTest 15 test/82 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-FINALIZE-BUTTON: pulihkan koneksi tombol pengesahan
- Tombol Sahkan kehadiran kembali terhubung ke form input peserta melalui atribut form yang eksplisit.
- Gaya tombol modern kembali diterapkan dan submit membawa seluruh status peserta untuk disimpan lalu ditandai VALIDATED.
- Verifikasi: StudentAttendanceUiTest dan StudentAttendanceFinalizerTest 15 test/82 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-CONTEXT-NAVIGATION: pertahankan filter sesi
- Halaman detail Kehadiran Santri kini memiliki tautan kembali ke daftar sesi.
- Menu Kontrol Kehadiran dari halaman detail membawa filter bulan sesi, kelas sesi, dan urutan terbaru secara otomatis.
- Verifikasi: StudentAttendanceUiTest dan AttendanceExceptionUiTest 16 test/99 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-TEACHER-LABEL: bedakan guru dan petugas
- Header detail kehadiran kini menampilkan Guru pengajar dari penugasan jadwal dan Diisi oleh/Diperiksa oleh dari akun petugas secara terpisah.
- Sumber data jadwal tidak diubah; perbaikan mencegah petugas pencatat disalahartikan sebagai guru pengajar.
- Verifikasi: StudentAttendanceUiTest dan AttendanceExceptionUiTest 16 test/101 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-ACTOR-LABEL: perjelas petugas absen
- Label petugas pada halaman pengisian diubah dari “Diisi oleh” menjadi “Diabsen oleh”; halaman hasil tetap menggunakan “Diperiksa oleh”.
- Verifikasi: StudentAttendanceUiTest 9 test/70 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-MOBILE-SPACING: amankan konten mobile
- Konten detail kehadiran diberi ruang aman dari sidebar fixed pada mobile agar judul, metadata, dan kartu kiri tidak tertutup.
- Kartu ringkasan ditata satu kolom pada mobile untuk keterbacaan yang lebih baik; tabel peserta tetap scroll horizontal.
- Verifikasi: StudentAttendanceUiTest dan AttendanceExceptionUiTest 16 test/102 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-DAY-LABEL: tampilkan nama hari
- Metadata waktu sesi kini menampilkan nama hari dalam Bahasa Indonesia sebelum tanggal dan jam.
- Verifikasi: StudentAttendanceUiTest dan AttendanceExceptionUiTest 16 test/103 assertion serta Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-ATTENDANCE-CORRECTION-UI: sediakan koreksi pasca-pengesahan
- Sesi final yang berada pada periode terkunci kini menyediakan formulir pengajuan koreksi per santri tervalidasi.
- Halaman hasil Waka menampilkan permintaan koreksi aktif dengan aksi Setujui, Tolak, dan Terapkan.
- Endpoint menggunakan service koreksi existing, versioning, otorisasi peran, dan audit; data final tidak diedit langsung.
- Verifikasi: PostLockAttendanceCorrectionServiceTest, StudentAttendanceUiTest, dan AttendanceExceptionUiTest 20 test/113 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-CORRECTION-NAV: tambahkan akses koreksi
- Sidebar Waka/Super Admin kini memiliki menu Hasil & Koreksi untuk membuka daftar permintaan koreksi.
- Verifikasi: PostLockAttendanceCorrectionTest dan StudentAttendanceUiTest 13 test/81 assertion serta Blade cache lulus.
# 2026-09-10 — IMP-ACADEMIC-WAKA-DIRECT-ATTENDANCE-CORRECTION: beri kewenangan Waka
- Waka Akademik kini dapat mengoreksi langsung status kehadiran tervalidasi dari halaman Periksa hasil.
- Koreksi langsung tetap mewajibkan alasan, expected version, validasi status, dan audit action khusus Waka.
- Verifikasi: PostLockAttendanceCorrectionServiceTest dan StudentAttendanceUiTest 14 test/84 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-WAKA-CORRECTION-SERVICE-BINDING: perbaiki endpoint koreksi
- Import service koreksi ditambahkan pada controller agar dependency injection endpoint direct correction menggunakan namespace domain yang benar.
- Verifikasi: PostLockAttendanceCorrectionServiceTest dan StudentAttendanceUiTest 14 test/84 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-WAKA-CORRECTION-TABLE-NAME: selaraskan validasi attendance
- Validasi attendance_id pada pengajuan dan koreksi langsung kini memakai tabel canonical `student_attendance` sesuai model dan migration.
- Verifikasi: PostLockAttendanceCorrectionServiceTest dan StudentAttendanceUiTest 14 test/84 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-REVIEW-RESPONSIVE: rapikan form koreksi mobile
- Panel dan form koreksi hasil kehadiran diberi batas lebar aman, `box-sizing` konsisten, dan pembungkusan field agar tidak meluber pada viewport kecil.
- Tombol pengajuan koreksi menjadi selebar kontainer pada mobile; tabel hasil tetap menggunakan scroll horizontal terkontrol untuk kolom yang banyak.
- Verifikasi: StudentAttendanceUiTest dan PostLockAttendanceCorrectionServiceTest 14 test/84 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-CORRECTION-FLOW-LABEL: jelaskan koreksi langsung Waka
- Aksi pada halaman Periksa hasil diubah menjadi “Terapkan koreksi” karena koreksi Waka diterapkan langsung, bukan masuk antrean persetujuan.
- Daftar Hasil & Koreksi kini menjelaskan bahwa daftar kosong berarti tidak ada permintaan aktif; koreksi langsung sudah selesai saat formulir dikirim.
- Verifikasi: StudentAttendanceUiTest dan PostLockAttendanceCorrectionServiceTest 14 test/84 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-BULK-STATUS: pastikan status input berubah
- Tombol “Tandai semua hadir/tertib” kini langsung mengubah dropdown, memicu event perubahan, dan menampilkan feedback jumlah santri.
- Perubahan status individual langsung menampilkan “Draf (belum disimpan)” agar pengguna memahami bahwa tombol Simpan sementara masih diperlukan.
- Verifikasi: StudentAttendanceUiTest dan StudentAttendanceFinalizerTest 15 test/87 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-SICK-STATUS: tambahkan status sakit
- Status `SICK`/“Sakit” tersedia pada pengisian kehadiran dan koreksi pasca-pengesahan.
- Validasi finalisasi dan service koreksi menerima status Sakit; ringkasan sesi, daftar review, dan ekspor laporan ikut menampilkannya.
- Verifikasi: StudentAttendanceUiTest, StudentAttendanceFinalizerTest, PostLockAttendanceCorrectionServiceTest, dan AttendanceSemanticMetricsServiceTest 21 test/107 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-RESULTS-RESPONSIVE: siapkan halaman hasil untuk tablet
- Halaman Hasil Kehadiran Santri kini memakai breakpoint tablet dengan ringkasan dua kolom, metadata sesi yang membungkus aman, dan kontainer tabel yang dapat digeser tanpa melebarkan halaman.
- Pada ponsel, ringkasan kembali satu kolom dan kartu/form tetap mengikuti lebar layar.
- Verifikasi: StudentAttendanceUiTest dan AttendanceExceptionUiTest 16 test/103 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-RESULT-CARDS: ubah tabel hasil menjadi kartu responsif
- Tabel hasil baca-saja kini berubah menjadi kartu per santri pada tablet dan ponsel dengan label kolom yang tetap terlihat, termasuk Status pemeriksaan.
- Tampilan desktop tetap memakai tabel; tabel input operasional tidak terpengaruh dan tetap dapat digeser bila diperlukan.
- Verifikasi: StudentAttendanceUiTest dan AttendanceExceptionUiTest 16 test/103 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-RESULT-SPACING: pisahkan aksi dan kartu hasil
- Jarak vertikal ditambahkan antara tombol unduh dan kartu santri pertama pada halaman hasil agar blok tidak menempel.
- Verifikasi: StudentAttendanceUiTest 9 test/71 assertion serta Blade cache lulus.
# 2026-09-10 — FIX-ACADEMIC-ATTENDANCE-INPUT-CARDS: responsifkan tabel input
- Tabel pengisian kehadiran kini berubah menjadi kartu per santri pada tablet/mobile, dengan label field dan kontrol yang tidak terpotong.
- Desktop tetap mempertahankan tabel input lengkap; fungsi penyimpanan dan finalisasi tidak berubah.
- Verifikasi: StudentAttendanceUiTest 9 test/71 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-DATE-LABELS: konsistenkan label waktu dashboard
- Label hari pada daftar sesi dashboard Waka kini menggunakan Bahasa Indonesia secara eksplisit.
- Format waktu sesi dibuat stabil `dd/mm/yyyy, HH:mm`, sedangkan tren memakai singkatan bulan Indonesia tanpa bergantung pada locale server.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/90 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-FILTER-LOCALE: selaraskan periode dashboard
- Dropdown bulan, teks periode, dan judul ringkasan bulanan kini selalu menggunakan nama bulan Bahasa Indonesia.
- Judul ringkasan bulanan tidak lagi hardcode Juli sehingga tetap benar saat periode lain dipilih.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/91 assertion serta Blade cache lulus.
# 2026-09-11 — UAT-ACADEMIC-DASHBOARD-FILTER-CONSISTENCY: tutup audit filter periode
- Audit route dashboard memastikan pemilihan bulan mengabaikan tanggal manual yang sudah usang dan format periode tetap konsisten.
- Konteks rentang grafik serta ekspor tetap dipertahankan; tidak ada perubahan pada sumber transaksi kehadiran.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/91 assertion serta Blade cache lulus.
# 2026-09-11 — UAT-ACADEMIC-DASHBOARD-RESPONSIVE: verifikasi viewport dashboard
- Dashboard diverifikasi pada ukuran default, tablet 1024×768, dan mobile 390×844.
- Tidak ditemukan horizontal overflow; lebar dokumen mengikuti viewport dan sidebar/content tetap berada dalam batas layar.
- Viewport sementara dikembalikan ke ukuran normal; tidak ada perubahan database atau transaksi.
# 2026-09-11 — FIX-ACADEMIC-SIDEBAR-ACCESSIBILITY: perjelas navigasi ikon
- Semua tautan sidebar kini memiliki `aria-label` dan tooltip, sementara ikon hanya diperlakukan sebagai dekorasi.
- Verifikasi browser memastikan screen reader tree menampilkan nama menu seperti Beranda, Kontrol Kehadiran, Jadwal, dan Laporan.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/92 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-SIDEBAR-MOBILE-TOGGLE: sediakan kontrol navigasi mobile
- Sidebar mobile kini memiliki tombol Buka navigasi/Tutup navigasi yang dapat diakses keyboard dan screen reader.
- Status `aria-expanded` serta label tombol diperbarui saat sidebar dibuka atau ditutup; rute menu dan otorisasi tetap sama.
- Verifikasi browser mobile menunjukkan tombol berubah dari collapsed menjadi expanded dan footer navigasi tampil.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/93 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-SIDEBAR-MOBILE-DISMISS: sederhanakan penutupan navigasi
- Sidebar mobile kini dapat ditutup dengan tombol `Escape` atau klik di luar area sidebar.
- Status `aria-expanded`, label, tooltip, dan fokus tombol dikembalikan saat sidebar ditutup.
- Verifikasi browser mobile: buka sidebar lalu tekan `Escape` berhasil mengembalikan tombol ke status collapsed.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/94 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-SIDEBAR-CURRENT-PAGE: tandai menu aktif
- Menu sidebar aktif kini memiliki `aria-current="page"` secara kondisional.
- Verifikasi DOM dashboard memastikan Beranda ditandai sebagai halaman aktif tanpa mengubah rute atau otorisasi.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/95 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-SIDEBAR-FILTER-CONTEXT: pertahankan periode saat navigasi
- Tautan logo, Beranda, dan Pemantauan Kelas kini membawa filter dashboard yang relevan agar periode aktif tidak hilang.
- Parameter yang dipertahankan hanya `month`, `from`, `to`, `trend_days`, dan `semester_id`; parameter halaman lain tidak ikut terbawa.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/96 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-CANONICAL-PERIOD: bersihkan URL periode
- Dashboard kini mengarahkan URL yang memiliki `month` sekaligus `from/to` lama ke URL canonical yang hanya membawa bulan dan filter relevan.
- Periode data dan URL kini selaras; verifikasi langsung menunjukkan URL Juli kembali menjadi `?month=2026-07` dengan tanggal Juli di form.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/98 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-REPORT-LINK-LABEL: jelaskan cakupan laporan
- Menu sidebar yang menuju modul laporan Juli kini berlabel `Laporan Juli 2026` agar cakupannya jelas saat dashboard melihat periode lain.
- Rute laporan tetap khusus Juli sesuai sumber laporan yang tersedia; tidak ada klaim bahwa laporan bulan lain sudah tersedia.
- Verifikasi browser memastikan accessibility tree menampilkan `Laporan Juli 2026`.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/99 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-TEACHER-KPI-LABEL: perjelas data kehadiran guru
- KPI kehadiran guru kini membedakan tidak adanya data kehadiran guru dari tidak adanya sesi selesai.
- Sesi santri tetap dapat ditampilkan/diukur meskipun partisipasi kehadiran guru belum tercatat.
- Verifikasi browser menunjukkan label `Belum ada data kehadiran guru` pada kondisi yang sesuai.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/100 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-SESSION-LABEL: luruskan makna ringkasan sesi
- Kartu yang menampilkan persentase sesi disahkan kini berjudul `Pengesahan sesi`, bukan `Kehadiran santri`.
- Judul panel diubah menjadi `Status Sesi Periode Terpilih` agar indikator pengesahan tidak tertukar dengan transaksi kehadiran santri.
- Verifikasi browser dashboard Juli memastikan istilah baru tampil dan angka tetap sama.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/102 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-TEACHER-SCOPE-LABEL: perjelas cakupan KPI guru
- Catatan KPI kehadiran guru kini menyebut `penugasan guru tercatat`, sesuai fakta bahwa metrik menghitung partisipasi penugasan pada sesi.
- Tidak ada perubahan pada perhitungan, sumber transaksi, database, atau otorisasi.
- Verifikasi browser dashboard Juli menunjukkan label baru dan nilai tetap konsisten.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/103 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-ARIA-PROGRESS: aksesibilitaskan indikator visual
- Bar kelengkapan kelas dan tren kehadiran kini memakai `role="progressbar"` dengan batas nilai dan nilai/status ARIA.
- Nilai yang belum tersedia tetap diumumkan sebagai status, bukan dipaksakan menjadi nol secara semantik.
- Verifikasi browser accessibility tree menampilkan progress indicator beserta nilai persentasenya.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/105 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-ARIA-CLASS-SCOPE: spesifikkan label progress kelas
- Label ARIA progress bar kelas kini menyertakan nama kelas agar indikator berulang dapat dibedakan pembaca layar.
- Nilai kelengkapan, status belum tersedia, dan tampilan visual tetap sama.
- Verifikasi browser dashboard default menampilkan label seperti `Kelengkapan kehadiran Kelas 1`.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/106 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-MONTH-LABEL: rapikan pilihan bulan
- Label dropdown bulan kini hanya menampilkan nama bulan dan tahun, tanpa tanggal sintetis `01`.
- Nilai query `YYYY-MM`, periode tanggal, dan perilaku filter tetap dipertahankan.
- Verifikasi browser dashboard Juli menampilkan `Juli 2026` dan `Agustus 2026`.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/106 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-MONTH-HELP: jelaskan mode filter bulan
- Filter bulan kini memiliki petunjuk visible dan `aria-describedby` yang menjelaskan perbedaan mode bulan penuh dan rentang tanggal manual.
- Nilai query, resolver periode, dan data dashboard tetap tidak berubah.
- Verifikasi browser accessibility tree menampilkan petunjuk filter lengkap.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/107 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-FILTER-MODE: tampilkan mode filter aktif
- Ringkasan periode kini menyebut `Mode bulan penuh` atau `Mode rentang manual` secara eksplisit.
- Perilaku query dan pemilihan periode tidak berubah.
- Verifikasi browser dashboard Juli menampilkan `Periode: 01 Jul 2026–31 Jul 2026 · Mode bulan penuh`.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/108 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-APPLY-BUTTON: rapikan aksi penerapan filter
- Tombol Terapkan kini memiliki ukuran minimum yang konsisten, radius dan shadow yang proporsional, serta state hover/active/focus yang jelas.
- Posisi tombol tetap sejajar dengan input tanggal pada desktop/tablet dan memenuhi lebar pada mobile.
- Verifikasi screenshot browser menunjukkan tombol lebih seimbang dengan kontrol filter.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/108 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-APPLY-BUTTON-HEIGHT: samakan tinggi tombol filter
- Tinggi tombol Terapkan kini dikunci agar sejajar dengan input tanggal di sampingnya.
- Teks tombol tetap terpusat, state interaksi tetap dipertahankan, dan layout mobile tidak berubah.
- Verifikasi screenshot browser menunjukkan tinggi tombol dan input lebih seragam.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/108 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-APPLY-BUTTON-ALIGNMENT: sejajarkan posisi tombol filter
- Offset vertikal tombol Terapkan disesuaikan agar garis atas dan bawahnya sejajar dengan input tanggal pada viewport lebar.
- Ukuran, state interaksi, dan perilaku mobile tetap dipertahankan.
- Verifikasi screenshot browser menunjukkan tombol sejajar dengan kontrol di sampingnya.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/108 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-FILTER-LAYOUT: rapikan grid filter
- Filter dashboard memakai layout grid stabil untuk desktop dan tablet.
- Pada tablet, dropdown bulan berada di baris atas; tanggal mulai, tanggal selesai, dan tombol Terapkan sejajar; ringkasan periode berada di baris bawah.
- Pada mobile, filter tetap berubah menjadi satu kolom.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/108 assertion serta Blade cache lulus; screenshot browser tablet terverifikasi.
# 2026-09-11 — FIX-ACADEMIC-DASHBOARD-FILTER-ALIGNMENT: sejajarkan kontrol filter
- Label bulan, mulai, dan sampai kini dimulai pada garis atas yang sama.
- Tombol Terapkan sejajar dengan input tanggal pada desktop/tablet; mobile tetap satu kolom.
- Ringkasan periode tetap berada di baris terpisah agar tidak menempel pada kontrol.
- Verifikasi: AcademicRoleDashboardServiceTest dan TeacherAttendanceServiceTest 29 test/108 assertion serta Blade cache lulus; screenshot browser terverifikasi.
# 2026-09-11 — FIX-ACADEMIC-EXCEPTIONS-BULK-ACTION-ORDER: urutkan tindakan setelah temuan
- Blok Pembatalan Sesi Massal dipindahkan setelah Fokus daftar temuan dan tabel temuan.
- Endpoint, validasi, hak akses, dan isi form tetap sama; hanya urutan layout/DOM yang berubah.
- Verifikasi browser menunjukkan Fokus daftar temuan dan tabel tampil lebih dulu, lalu Pembatalan Sesi Massal.
- Verifikasi: AttendanceExceptionUiTest dan StudentAttendanceUiTest 16 test/103 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-TEACHER-ATTENDANCE-GUARD: pastikan pengganti hadir sebelum pengesahan
- Pengesahan absensi santri kini menolak sesi ketika guru utama berstatus Tidak hadir, Sakit, Izin, atau Lainnya tetapi belum ada guru pengganti/Wali Kelas yang berstatus Hadir.
- Status sesi tetap PLANNED dan absensi santri tetap DRAFT saat validasi gagal; sesi pembatalan resmi tetap mengikuti aturan pembatalan.
- Redirect pencatatan kehadiran guru dan guru pengganti mempertahankan posisi pada panel Rekap kehadiran guru.
- Verifikasi: StudentAttendanceFinalizerTest, StudentAttendanceUiTest, dan TeacherAttendanceServiceTest 22 test/108 assertion lulus serta Blade cache lulus.
# 2026-09-11 — FIX-ACADEMIC-TEACHER-ATTENDANCE-WAKA-AUTH: selaraskan akses Waka
- Waka Akademik kini dapat mencatat kehadiran guru pada seluruh kelas dalam scope akademiknya; Wali Kelas tetap dibatasi pada assignment efektif.
- Redirect tetap mempertahankan posisi pengguna pada panel Rekap kehadiran guru.
- Verifikasi: StudentAttendanceFinalizerTest, StudentAttendanceUiTest, dan TeacherAttendanceServiceTest 23 test/111 assertion lulus serta Blade cache lulus.
# 2026-09-11 — FIX-P0-A-HISTORICAL-WRITE-PROTECTION: tutup celah tulis histori absensi
- Jalur normal draft kini hanya menerima sesi PLANNED/CONFIRMED, attendance baru atau DRAFT, serta periode OPEN.
- Sesi COMPLETED/CANCELLED/RESCHEDULED, attendance VALIDATED, dan periode LOCKED ditolak pada service layer sebelum mutation.
- Verifikasi: StudentAttendanceDraftServiceTest 8 test/37 assertion; StudentAttendanceUiTest 12 test/84 assertion; correction regression 8 test/19 assertion; Blade cache lulus.
- P0-B sengaja tidak disentuh.
# 2026-09-11 — FIX-P0-B-TEACHER-FINALIZATION-GATE: wajibkan coverage guru sebelum finalisasi
- StudentAttendanceFinalizer kini mewajibkan expected PRIMARY, status guru utama yang resolved, dan SUBSTITUTE PRESENT untuk PRIMARY non-present.
- TeacherAttendanceService juga menolak mutasi langsung pada session COMPLETED/CANCELLED/RESCHEDULED.
- Fixture finalizer/UI diperbaiki agar sesi valid selalu memiliki PRIMARY PRESENT; tidak ada fakta historis yang dihapus atau diinferensikan.
- Verifikasi: StudentAttendanceFinalizerTest, StudentAttendanceUiTest, dan TeacherAttendanceServiceTest 28 test/133 assertion lulus serta Blade cache lulus.
# 2026-09-11 — FIX-P0-HTTP-REGRESSION-HARDENING: buktikan proteksi P0 melalui HTTP
- Draft endpoint kini menerjemahkan domain rejection menjadi redirect error terkontrol, bukan 500.
- Regression HTTP mencakup session COMPLETED, VALIDATED, LOCKED, teacher gate, cancellation, correction, substitution, dan normal Wali flow.
- Verifikasi: 48 test/250 assertion lulus serta Blade cache lulus.
- Phase 3 selesai; Phase 4 belum dimulai.
# 2026-09-11 — FIX-P0-ATTENDANCE-INTEGRITY-CLOSEOUT: tutup P0 lokal/UAT
- Final regression P0 lulus 48 test/250 assertion; broader Academic regression lulus 194 test/771 assertion; Blade cache lulus.
- Fixture HistoricalHomeroomHandover diperbarui agar memiliki PRIMARY PRESENT sesuai invariant finalisasi.
- P0 Attendance Integrity berstatus CLOSED_LOCAL_UAT; production readiness tetap tertunda sampai staging verification.
# 2026-09-11 — SAFE CHECKPOINT POST-P0: handoff sebelum P1
- Working tree/source consistency diverifikasi melalui source tree dan manifest; seluruh regresi Academic lulus 194 test/771 assertion tanpa failure.
- `HistoricalHomeroomHandoverServiceTest.php` hanya menerima fixture/regression adjustment untuk membuat PRIMARY PRESENT yang diwajibkan finalisasi; business rule handover tidak berubah.
- Tidak ada migration/schema, seed/import, RBAC, schedule, atau historical data rewrite.
- Metadata Git tidak tersedia di workspace ini; status/diff dan commit checkpoint tidak dapat dibuat tanpa menginisialisasi history baru. P1 tidak dimulai.
# 2026-09-11 — P1 PHASE 1: sinkronisasi policy/ADR/RBAC documentation
- Tujuh dokumen policy aktif dan ADR register disinkronkan dengan management policy: `WAKA_AKADEMIK` full Academic authority dan `SUPER_ADMIN` full authority lintas domain aktif.
- Dokumentasi menegaskan full authority tetap tunduk pada scope, resource state, workflow, approval, audit, versioning, lock dan publication; full authority bukan auto-approve atau silent historical edit.
- AI governance tetap permission/capability-based, human-confirmed, audited, dan tidak boleh bypass domain workflow. Target permission `academic.domain.manage` dan `platform.institution.manage` hanya didokumentasikan; rows/seeder tidak diubah.
- `archive/P12_HANDOFF_v1.0_COMBINED_SNAPSHOT.md` dan seluruh application source, seeder, migration, database, serta behavior tests tidak disentuh.
- Active policy contradiction search PASS; sisa wording lama hanya historical `CHANGELOG.md`/`archive` dan dipertahankan. P1 Phase 2 belum dimulai.
# 2026-09-11 — FIX-ACADEMIC-TEACHER-ATTENDANCE: rekap kehadiran guru per sesi
- Form rekap guru ditambahkan pada halaman sesi dengan status Hadir, Tidak hadir, Sakit, Izin, dan Lainnya; alasan wajib untuk status selain Hadir.
- Sesi resmi yang dibatalkan tetap tidak masuk evaluasi guru; sesi yang berjalan dengan guru pengganti/wali kelas mempertahankan participation guru terpisah dan tetap dapat mengisi absensi santri.
- Dashboard Waka kini menampilkan rincian Hadir, Tidak hadir, Sakit, Izin, dan Lainnya dari transaksi live.
- Pilihan guru pengganti kini default ke Wali Kelas aktif; catatan pengganti dapat memuat kegiatan seperti Ngaji Auditorium, sementara guru lain tetap dapat dipilih.
- Akses cepat “Catat kehadiran guru” ditambahkan setelah ringkasan santri dan mengarah ke form rekap di halaman yang sama; pada mobile tombol menjadi penuh.
- Verifikasi: 41 test/191 assertion serta Blade cache lulus.
# 2026-09-11 — FIX-P1-ACADEMIC-AUTHORIZATION-FOUNDATION: centralisasi otorisasi Academic
- Menambahkan `AcademicAuthorizationService` berbasis permission, role efektif, effective date, dan scope yang sudah ada tanpa migration/schema change.
- Waka mendapat full authority Academic melalui `academic.domain.manage`; Super Admin mendapat institution-wide authority melalui `platform.institution.manage`; Wali Kelas tetap hanya pada homeroom efektif.
- Menyelaraskan attendance, period lock, dashboard, historical handover, exception monitor, dan entry point Admin terkait; P0 state guards tetap dipertahankan.
- HistoricalHomeroomHandover hanya berubah pada evaluasi aktor as-of tanggal sesi; business rule handover dan fixture tidak diubah.
- Verifikasi: targeted 62 test/290 assertion dan full Academic 199 test/780 assertion lulus; Phase 3 belum dimulai.
# 2026-09-11 — FIX-P1-TEACHER-ATTENDANCE-CONCURRENCY: kunci resource session sebelum rekap guru
- TeacherAttendanceService kini memulai transaksi sebelum membaca state authoritative, mengunci `ClassSession`, recheck state, memvalidasi kepemilikan participation, memakai AcademicAuthorizationService, lalu mengunci participation.
- Sesi `COMPLETED`, `CANCELLED`, dan `RESCHEDULED`, stale caller, serta participation lintas session ditolak sebelum mutation/audit.
- Lock order dibandingkan dengan StudentAttendanceDraftService dan StudentAttendanceFinalizer: konsisten `ClassSession → dependent rows`.
- SQLite lokal tidak membuktikan contention PostgreSQL; real concurrency test ditunda ke staging.
- Verifikasi: targeted 41 test/215 assertion dan full Academic 203 test/792 assertion lulus; Blade cache lulus. P1 Phase 4 belum dimulai.
# 2026-09-11 — FIX-P1-SUBSTITUTION-CONCURRENCY: lindungi konflik guru pengganti
- SubstitutionService kini memeriksa candidate sebagai PRIMARY dan EXPECTED SUBSTITUTE pada seluruh sesi aktif yang intervalnya overlap.
- Final check berjalan setelah lock/reload target ClassSession, state recheck, authorization, dan PostgreSQL advisory transaction lock per candidate teacher.
- Interval mengikuti half-open `[start, end)`; CANCELLED/RESCHEDULED tidak menjadi false conflict; PRIMARY dan teaching assignment asli tetap utuh.
- Existing class/time exclusion constraint diaudit hanya melindungi class overlap, bukan candidate teacher substitution.
- Verifikasi: targeted 39 test/219 assertion dan full Academic 207 test/803 assertion lulus; Blade cache lulus. Real PostgreSQL concurrency test ditunda ke staging. P1 Phase 5 belum dimulai.
# 2026-09-11 — FIX-P1-CORRECTION-CONCURRENCY: kunci review dan apply koreksi
- Review correction request kini memakai AcademicAuthorizationService, transaction, row lock CorrectionRequest, PENDING recheck, version increment sekali, dan audit keputusan sekali.
- Apply approved kini mengunci CorrectionRequest lalu StudentAttendance, memvalidasi expected version/locked period, menandai request APPLIED atomik, dan menolak duplicate apply.
- Direct override Waka/Super pada attendance locked ditutup; correction locked wajib melalui request/review/apply workflow. Open-period correction service tetap terpisah.
- Verifikasi: targeted 31 test/181 assertion dan full Academic 209 test/811 assertion lulus; Blade cache lulus. Real PostgreSQL review concurrency ditunda ke staging. P1 Phase 6 belum dimulai.
# 2026-09-11 — FIX-P1-POSTGRES-CONTROLLED-VOCABULARY: defense-in-depth database checks
- Inventory vocabulary Academic dilakukan dari migration, model/service validation, controller, seeder, dan fixture sebelum migration ditulis.
- Migration forward-only menambahkan sembilan named PostgreSQL CHECK constraints pada class_sessions, session_teacher_participations, dan student_attendance; nullable attendance status tetap menerima NULL.
- Migration tidak dijalankan pada database production-style; SQLite local sengaja melewati SQL PostgreSQL dan tidak diklaim sebagai proof constraint.
- PostgreSQL distinct-value audit dan valid/invalid/NULL constraint proof ditunda ke staging; tidak ada data rewrite atau seed execution.
- Verifikasi: targeted 49 test/153 assertion dan full Academic 209 test/811 assertion lulus; Blade cache lulus. P1 Phase 7 belum dimulai.
# 2026-09-11 — FIX-P1-FINAL-REGRESSION-STAGING-HANDOFF: final audit P1
- Final source audit dan regresi lokal P1 selesai tanpa perubahan runtime baru: Academic 209 test/811 assertion dan Shared/Auth 62 test/263 assertion lulus; Blade cache lulus.
- PostgreSQL vocabulary query read-only dan staging runbook ditambahkan; migration, seeder, database, dan deployment tidak dijalankan.
- Finding staging: `ConsolidateAcademicRolesSeeder` masih menghapus assignment/role pilot sehingga bootstrap belum non-destructive; `SwapService` belum memakai advisory lock substitution sehingga cross-path teacher obligation tetap OPEN_STAGING_RISK.
- P1 local source/regression gate CLEARED, staging/production gate belum cleared. P2 tidak dimulai.
# 2026-09-11 — FIX-P1-R1-AUTHORITY-BOOTSTRAP-SAFETY: hilangkan cleanup destruktif dari bootstrap
- Audit menemukan cleanup pilot assignment dan penghapusan `ADMIN_AKADEMIK` aktif di `ConsolidateAcademicRolesSeeder::run()`; keduanya bukan migration-only behavior.
- Cleanup dihapus dari bootstrap. Seeder kini additive/idempotent untuk dua permission authority dan tidak mengubah role, assignment, permission existing, atau effective dates. Legacy cleanup ditandai `LEGACY_RBAC_CLEANUP_DEFERRED`.
- Test isolated bootstrap lulus 8 test/30 assertion; Academic 209 test/811 assertion; Shared/Auth 63 test/272 assertion; Blade cache dan Pint lulus. Tidak ada real seed, migration, atau database production/staging execution.
- P1-R2 belum dimulai.
# 2026-09-11 — FIX-P1-R1-RBAC-SIMPLIFICATION: retire ADMIN_AKADEMIK dari active authorization
- Keputusan governance baru menetapkan `ADMIN_AKADEMIK` retired. Audit active runtime source menemukan tidak ada authorization allow berbasis role tersebut; RBAC matrix diperbarui, sedangkan archive/manifest histori dipertahankan.
- Test negative authority ditambahkan: user dengan role `ADMIN_AKADEMIK` saja tidak memperoleh full Academic authority dan tidak dipromosikan otomatis. Seeder preservation test memastikan role, permission, assignment, dan pilot assignment existing tetap ada.
- Verifikasi: targeted 14 test/58 assertion; Academic 210 test/812 assertion; Shared/Auth 63 test/273 assertion; Admin 44 test/164 assertion; Blade cache dan Pint lulus. Tidak ada migration, real seed, atau database role migration.
- Active assignment aktual tidak inspectable tanpa database nyata; wajib review sebelum staging. P1-R2 belum dimulai.
# 2026-09-11 — FIX-P1-R2-CROSS-PATH-TEACHER-OBLIGATION: satukan serialisasi writer kewajiban guru
- Inventory writer mencakup substitution, swap, reschedule, extra/ad-hoc session, session generator, primary participation recorder, dan boundary schedule rule/import.
- Ditambahkan `TeacherObligationLockService` dengan namespace deterministik dan `hashtextextended(..., 0)` PostgreSQL 64-bit transaction advisory lock; multi-teacher lock memakai urutan ID canonical.
- Substitution, swap, reschedule, extra session, generator, dan primary participation recorder kini memakai lock/final conflict check yang kompatibel. Conflict universe mencakup PRIMARY dan EXPECTED SUBSTITUTE, interval half-open, tanpa mengubah lineage/business vocabulary.
- Verifikasi: targeted 27 test/92 assertion; Academic 213 test/817 assertion; Pint dan Blade cache lulus. Real PostgreSQL cross-path contention tetap DEFERRED_TO_STAGING. P1-R3 belum dimulai.
# 2026-09-12 — FIX-P1-R4-CONTROLLED-VOCABULARY: koreksi contract sebelum staging
- Keputusan manajemen menetapkan IMTAQ PILOT GOVERNANCE MODE aktif: WAKA_AKADEMIK full Academic authority, SUPER_ADMIN full institution authority, ADMIN_AKADEMIK retired, tanpa privilege restriction baru dan tetap dengan perlindungan audit/version/history.
- Audit actual source mengonfirmasi `FULL_CLASS` dan `SELECTED_STUDENTS` sebagai participant scope; `JOINT_SCOPE` hanya `scope_role` pada grouping; writer runtime memakai `TEACHING_ASSIGNMENT` untuk PRIMARY dan `REPLACEMENT` untuk SUBSTITUTE. `SUBSTITUTION` tetap change type, bukan obligation type.
- Migration P1 yang belum diterapkan dikoreksi minimum; `ExtraSessionCreator` kini menolak participant scope invalid sebelum persistence. Fixture substitute lama dinormalkan ke `REPLACEMENT`.
- Read-only PostgreSQL SQL, staging runbook, schema/RBAC documentation, impact record, dan R4 manifest disinkronkan. Tidak ada migration execution, seed, database write, atau historical rewrite.
- P1-R4 siap untuk pre-staging re-audit; staging dan P2 tetap ditunda oleh manajemen.

# 2026-09-12 — FIX-WAKA-1C-CANONICAL-ATTENDANCE-METRICS: implementasi semantic metrics canonical
- `AttendanceSemanticMetricsService` kini hanya menghitung opportunity `EXPECTED` dengan `is_required=true`; peserta optional tidak masuk denominator KPI wajib atau missing count.
- Physical presence dan unexcused absence memakai denominator `resolved`; completeness tetap `resolved/eligible`. Semantik null/zero diterapkan untuk eligible/resolved nol.
- Test semantic langsung diperbarui/ditambah untuk complete, missing, optional, resolved-zero, dan eligible-zero.
- Verifikasi: targeted 5 test/31 assertion; Academic 218 test/843 assertion; Pint terarah dan PHP lint lulus. Tidak ada downstream consumer, migration, database, seed, RBAC, schedule, atau historical data change.
- WAKA-1C selesai; menunggu review sebelum WAKA-1D.

# 2026-09-12 — FIX-WAKA-1D-DASHBOARD-METRIC-SEMANTICS: sinkronisasi aggregation dan trend
- `AcademicRoleDashboardService` menyelaraskan physical presence overview, grade-level, dan daily trend ke denominator `resolved`; completeness tetap `resolved/eligible`.
- Grade aggregation dibuktikan count-weighted dari total numerator/denominator, bukan rata-rata persentase kelas. Grain `EXPECTED + is_required=true`, eligibility sesi, dan controlled status tetap dipertahankan.
- Test langsung dashboard mencakup overview missing, grade weighted, trend missing, resolved-zero, dan eligible-zero.
- Verifikasi terakhir: targeted 30 test/119 assertion; Academic 222 test/859 assertion; Pint terarah dan PHP lint lulus. Tidak ada downstream failure, UI/export/database/migration/seed/RBAC/schedule/historical change.
- WAKA-1D selesai; menunggu review sebelum WAKA-1E.

# 2026-09-12 — FIX-WAKA-1F-ATTENDANCE-UI-SEMANTICS: perjelas makna metric attendance di dashboard
- Dashboard Waka kini menjelaskan denominator resolved pada Kehadiran fisik, menampilkan breakdown data wajib tervalidasi/belum tervalidasi pada Kelengkapan data, dan membedakan null dari 0%.
- Monitoring kelas tidak lagi menyebut student attendance opportunities sebagai sesi; trend menampilkan physical presence dan completeness harian bersama resolved/eligible context.
- Session completion, metric service, aggregation service, export, dan grade summary tetap tidak berubah.
- Verifikasi: targeted UI 36 test/137 assertion; Academic 228 test/877 assertion; view cache, Pint terarah, dan lint lulus. Tidak ada database write atau perubahan real data.
- WAKA-1F selesai; menunggu review sebelum WAKA-1G.
## 2026-09-12 — WAKA-1H KPI/export documentation synchronization
- Updated active attendance documentation (`KPI_DICTIONARY.md` and `BUSINESS_RULES.md`) to match canonical semantics: `ELIGIBLE`, `RESOLVED`, `MISSING`, physical presence and unexcused absence denominators, completeness, required/optional eligibility, and null/zero behavior.
- Documented the current stable machine-readable Academic Dashboard CSV pilot contract, ordered headers, row grain, percentage unit, blank/null behavior, compatibility decisions, and accepted filename/period limitations.
- No application behavior, CSV headers, tests, routes, controllers, migrations, database, seeders, RBAC, schedule logic, or historical/archive documentation changed.
- Verification: targeted repository search and documentation diff review PASS; no application tests required for documentation-only scope.

# 2026-09-12 — FIX-WAKA-2C-TODAY-SESSION-READ-MODEL: canonical operational Today read model
- Added backend-only `AcademicTodaySessionService` using the Asia/Jakarta calendar-day window and distinct `ClassSession` institutional grain.
- Added explicit operational state precedence, active/cancelled/rescheduled summary counts, source preservation, joint-session class deduplication, and Academic full-authority reuse.
- No dashboard/controller/route integration; student/teacher attendance, corrections, substitution, schedule, KPI, alert, calendar, migration, seed, RBAC, and historical data behavior remain unchanged.
- Verification: targeted 15 test/40 assertion; Academic 243 test/917 assertion; PHP lint and Pint PASS. WAKA-2D not started.

# 2026-09-12 — FIX-ATTENDANCE-TEACHER-STATUS-LABEL: clarify session teacher attendance label
- Renamed the repeated `Status guru` form label to `Status kehadiran pada sesi` so it is distinct from the scheduled teacher information in the session header.
- No form contract, route, validation, authorization, persistence, or business rule changed.
- Verification: Blade cache PASS; StudentAttendanceUiTest 16 test/145 assertion PASS; browser label verified twice.

# 2026-09-12 — FIX-ATTENDANCE-INDONESIAN-WEEKDAY: localize exception weekday labels
- Exception attendance dates now show Indonesian weekday names, including `Minggu`, in the findings table and bulk-date helper.
- Moved the shared weekday map before its first Blade use; date values, filters, and business behavior remain unchanged.
- Verification: Blade cache PASS; AttendanceExceptionUiTest 7 test/32 assertion PASS; browser AX verification confirmed Indonesian labels and no English weekday labels.

# 2026-09-12 — ATT-1A: prevent empty attendance/grooming draft side effects
- `StudentAttendanceController` now skips attendance and grooming service calls independently for new rows whose corresponding inputs are blank, including whitespace-only text.
- Existing attendance/grooming records continue through the existing services, and `StudentAttendanceDraftService` itself remains unchanged.
- Finalize uses the same orchestration guard and still rejects missing required attendance through the finalizer.
- Verification: StudentAttendanceUiTest 19 test/162 assertion; DraftService + Finalizer tests 19 test/78 assertion; PHP lint PASS.

# 2026-09-12 — ATT-1B: truthful unresolved teacher attendance display
- Teacher attendance selects now show `Belum dicatat` when the canonical stored status is `NULL`; `PRESENT`, `ABSENT`, `SICK`, `IZIN`, and `OTHER` remain unchanged.
- No validation, persistence, substitution, database, or GET side-effect behavior changed.
- Verification: StudentAttendanceUiTest 20 test/168 assertion; PHP lint PASS.

# 2026-09-12 — ATT-1C: complete student attendance session summary
- Attendance Session summary now shows Total, Hadir, Terlambat, Sakit, Izin, Dikecualikan, Tidak hadir, and Belum diisi as separate buckets.
- Summary remains a transaction/input-progress view over the loaded roster; `LATE` is not merged into `PRESENT`, and null/missing attendance remains pending.
- Verification: StudentAttendanceUiTest 22 test/183 assertion; Academic 249 test/955 assertion; view cache, PHP lint, and Pint PASS.

# 2026-09-12 — ATT-1D: authorize attendance session before GET initialization
- `StudentAttendanceController::show()` now resolves the authenticated user's lawful session context before loading/creating the primary teacher participation.
- Unauthorized GET requests cannot trigger `ensurePrimary()`; authorized Waka, Super Admin, and Wali Kelas access remains functional, with repeated GET remaining idempotent.
- Verification: StudentAttendanceUiTest 26 test/191 assertion; Academic 253 test/963 assertion; view cache, PHP lint, and Pint PASS.

# 2026-09-12 — ATT-1E: clarify attendance success feedback
- Attendance-session flash messages now appear as accessible auto-dismissing toasts with close controls, making teacher-attendance save confirmation easier to locate.
- Redirect, flash, validation, persistence, authorization, and business behavior remain unchanged.
- Verification: StudentAttendanceUiTest 26 test/195 assertion; view cache, PHP lint, and Pint PASS.

# 2026-09-13 — ATT-1F: remove duplicate teacher-attendance callout
- Removed the redundant “Guru tidak hadir?” quick-action block because the Rekap kehadiran guru form is directly visible below it.
- Teacher-attendance form, save action, routes, validation, persistence, and business behavior remain unchanged.
- Verification: StudentAttendanceUiTest 26 test/195 assertion; view cache, PHP lint, and Pint PASS.

# 2026-09-13 — ATT-1G: remove redundant attendance information blocks
- Removed the editable-session `Petunjuk` and `Perubahan guru sesi` cards so the attendance and teacher rekap forms are immediately visible.
- Kept the completed/read-only confirmation and preserved all forms, routes, validation, persistence, authorization, and business behavior.
- Verification: StudentAttendanceUiTest 26 test/195 assertion; view cache, PHP lint, and Pint PASS.

# 2026-09-13 — ATT-2A: attendance session visual hierarchy and density
- Attendance Session now prioritizes session identity, compact status summary, teacher rekap, and student roster; subject is visible and the teaching-assignment label is `Guru terjadwal`.
- Summary remains eight separate transaction buckets, while substitution/cancellation move into native `Tindakan sesi` disclosure without changing routes or business rules.
- Responsive summary is compact on desktop/tablet/mobile and roster helper copy matches stacked-card behavior.
- Verification: StudentAttendanceUiTest 26 test/196 assertion; Academic 253 test/968 assertion; view cache, PHP lint, and Pint PASS. Browser reload verification timed out; owner visual review remains pending.

# 2026-09-13 — ATT-2B: compact student roster and exception-based grooming
- Removed the editable `Tandai semua tertib` mass action and its dedicated JavaScript while preserving `Tandai semua hadir`.
- New participants now see a compact `+ Catat masalah ketertiban` disclosure; existing grooming records remain open with their current values, and meaningful old validation input reopens the disclosure.
- Workflow status is compacted into the santri identity area; attendance and grooming names, controlled vocabulary, persistence, and read-only semantics remain unchanged.
- Verification: StudentAttendanceUiTest 29 test/209 assertion; Academic 256 test/981 assertion; view cache, PHP lint, and Pint PASS. Browser visual verification not claimed; owner visual review remains pending.

# 2026-09-13 — ATT-2C1: modern Attendance Session visual foundation
- Applied scoped visual refinement to the Attendance Session page: calm white/neutral surfaces, restrained borders, clearer typography, compact summary tiles and workflow badges, consistent inputs, and differentiated action hierarchy.
- Added restrained roster row hover/dividers and visible hover/focus states without changing the ATT-2A responsive structure or ATT-2B grooming behavior.
- Verification: StudentAttendanceUiTest 29 test/209 assertion; Academic 256 test/981 assertion; view cache, PHP lint, and Pint PASS. Browser visual verification not claimed; screenshot review remains pending.

# 2026-09-13 — ATT-2C2: modern student roster components
- Student attendance notes now use native progressive disclosure: blank notes are compact `+ Catatan` actions, while existing notes and meaningful validation input remain visible/open.
- Student identity and workflow badge remain the primary row anchor; attendance controls stay native and all ATT-2B grooming behavior, field names, and read-only semantics are preserved.
- Verification: StudentAttendanceUiTest 31 test/220 assertion; Academic 258 test/992 assertion; view cache, PHP lint, and Pint PASS. Browser visual verification not claimed; screenshot review remains pending.

# 2026-09-13 — ATT-2C3: modern operational action bar
- Added a sticky editable-session action bar with live progress for canonical required participants, conservative teacher-gate messaging, and true disabled semantics for finalization.
- Draft save remains available as `Simpan draf`; finalize remains the same backend action; completed/read-only sessions do not receive the editable bar.
- Verification: StudentAttendanceUiTest 31 test/220 assertion; Academic 258 test/992 assertion; view cache, PHP lint, and Pint PASS. Browser visual verification not claimed; screenshot review remains pending.

# 2026-09-13 — ATT-2C3R: operational action bar visual refinement
- Refined action-bar composition into left-side progress/readiness copy and right-side natural-width actions on desktop, with balanced mobile actions.
- Replaced user-facing `resolved` wording with operational Indonesian while preserving the existing readiness state and finalization logic.
- Verification: StudentAttendanceUiTest 31 test/224 assertion; Academic 258 test/996 assertion; view cache, PHP lint, and Pint PASS. Browser visual verification not claimed; screenshot review remains pending.

# 2026-09-13 — ATT-2C2R: remove duplicate plus icons
- Simplified roster disclosure labels to `Catatan` and `Ketertiban` while retaining the existing circular plus icon affordances.
- No details behavior, form contract, JavaScript, backend, or data behavior changed.
- Verification: StudentAttendanceUiTest 31 test/224 assertion; Academic 258 test/996 assertion; view cache, PHP lint, and Pint PASS.

# 2026-09-13 — ATT-3A: hero session header
- Reorganized the Attendance Session hero header into a responsive metadata grid while preserving all existing session facts and navigation.
- Clarified `Guru terjadwal`, `Dicatat oleh`, and human-readable session status labels, including `Dijadwal ulang`.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/227 assertion; Academic 258 test/999 assertion; view cache, PHP lint, and Pint PASS. Browser visual verification remains for owner review; ATT-3B not started.

# 2026-09-13 — ATT-3B: modern attendance KPI summary tiles
- Refined the eight attendance summary tiles with compact accessible accents, restrained status palette, and responsive 8/4/2-column grids.
- Preserved all authoritative counts, status buckets, zero visibility, reconciliation, and server-rendered behavior; pending attendance receives only a neutral emphasis when non-zero.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/230 assertion; Academic 258 test/1002 assertion; view cache, PHP lint, and Pint PASS. ATT-3C not started.

# 2026-09-13 — ATT-3C: modern teacher attendance card
- Refined the teacher attendance section into responsive operational rows with initials avatar, human-readable role badge, compact status/note controls, and aligned save action.
- Added a read-only teacher summary for completed sessions without changing teacher status semantics, substitute rules, routes, validation, or persistence.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/230 assertion; Academic 258 test/1002 assertion; view cache, PHP lint, and Pint PASS. ATT-3D not started.

# 2026-09-13 — ATT-3D: attendance roster toolbar and client-side search
- Added a compact responsive roster toolbar with `Cari nama santri...` and minimal vanilla JS filtering by stable student-name attributes.
- Search is case-insensitive and partial-match; hidden rows and all form inputs remain preserved for submission, with a non-destructive empty state.
- Existing bulk attendance behavior and roster order remain unchanged; no backend or data changes.
- Verification: StudentAttendanceUiTest 31 test/237 assertion; Academic 258 test/1009 assertion; view cache, PHP lint, and Pint PASS. ATT-3E not started.

# 2026-09-13 — ATT-3E: modern roster styling alignment
- Refined student roster presentation with concise headers, source-preserved name typography, compact workflow badges, lighter row dividers, and hover/focus-within states.
- Preserved ATT-3D search, form fields, roster ordering, attendance/grooming semantics, bulk action, and mobile card transformation; no numbering or row menu added.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/239 assertion; Academic 258 test/1011 assertion; view cache, PHP lint, and Pint PASS. ATT-3F not started.

# 2026-09-13 — ATT-3F: modern attendance action footer
- Added a compact CSS progress ring to the existing sticky footer and refined its surface, spacing, hierarchy, and mobile layout.
- Preserved live filled/missing counts, readiness/teacher blockers, true disabled finalization, save/finalize forms, and read-only behavior.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/241 assertion; Academic 258 test/1013 assertion; view cache, PHP lint, and Pint PASS. ATT-3G not started.

# 2026-09-13 — ATT-3G: final attendance responsive reconciliation
- Reconciled existing responsive rules at the tablet breakpoint: teacher identity now safely precedes status/note controls, and the sticky action footer wraps without crowding.
- Preserved mobile stacking, desktop composition, ATT-3A through ATT-3F behavior, touch controls, data contracts, and sidebar scope.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/241 assertion; Academic 258 test/1013 assertion; view cache, PHP lint, and Pint PASS. Global Waka Shell not started.

# 2026-09-13 — ATT-3H: top attendance action cluster
- Added a compact top action cluster for bulk attendance, existing search, draft save, and finalize, using the same attendance form and finalize route as the sticky footer.
- Extended the existing readiness update to synchronize top and bottom finalize buttons; sticky footer remains the detailed status/safety control.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/243 assertion; Academic 258 test/1015 assertion; view cache, PHP lint, and Pint PASS. ATT-3I not started.

# 2026-09-13 — ATT-3HR: top attendance action cluster visual refinement
- Restored visible soft-green styling for `Tandai semua hadir` and normalized top Save/Finalize controls so they no longer render as browser-default buttons.
- Preserved ATT-3H form orchestration, readiness synchronization, search/bulk behavior, responsive layout, and ATT-3F footer.
- No backend, business logic, route, database, migration, RBAC, schedule, or real-data changes.
- Verification: StudentAttendanceUiTest 31 test/243 assertion; Academic 258 test/1015 assertion; view cache, PHP lint, and Pint PASS.

# 2026-09-13 — attendance exception bulk cancellation preview fix
- Prefilled the mass-cancellation form from the already-selected exception month/class filters so `Pratinjau` no longer stops on empty required date fields when operators work from the focused list.
- Preserved the separate preview/cancel payload, authority gate, candidate query, validation, locking, and cancellation business rules.
- Verification: targeted exception test 8 tests/36 assertions PASS; view cache and PHP lint PASS. Broader Academic regression and Pint run at closeout.

# 2026-09-13 — EXC-UI-1: exception page hierarchy, summary, and feedback
- Consolidated the page introduction into a precise hero and added four compact session-level backlog facets from the filtered `$exceptions` collection: total attention, missing roster, missing attendance, and unresolved attendance.
- Added accessible success and validation feedback near the page top while preserving the exception table, filters, mass actions, and all backend contracts.
- Verification: targeted exception test 10 tests/50 assertions; Academic regression 261 tests/1,033 assertions; view cache, PHP lint, and Pint PASS. EXC-UI-2 not started.

# 2026-09-13 — EXC-UI-2: modern filter command bar
- Reworked only the exception-list filter presentation into a compact command bar with active month/class/sort context, current filtered session count, and safe reset link.
- Preserved `list_month`, `list_class_id`, `list_sort`, filter behavior, hero/summary, table, mass actions, and all backend contracts.
- Verification: targeted exception test 10 tests/59 assertions; Academic regression 261 tests/1,042 assertions; view cache, PHP lint, and Pint PASS. EXC-UI-3 not started.

# 2026-09-13 — EXC-UI-3: modern exception queue and responsive cards
- Reworked only the exception queue presentation into a four-column desktop table and semantic compact mobile cards.
- Removed the queue scroll helper, clarified missing-attendance wording to `belum diisi`, preserved roster/unresolved findings, and refined normal/roster CTAs without changing routes or forms.
- Preserved EXC-UI-1/2 hierarchy, summaries, feedback, filters, mass cancellation, and all backend contracts.
- Verification: targeted exception test 11 tests/72 assertions; Academic regression 262 tests/1,055 assertions; view cache, PHP lint, and Pint PASS. Browser visual review remains pending; EXC-UI-4 not started.

# 2026-09-13 — EXC-UI-4: mass actions workspace and safe cancellation preview
- Moved the existing mass-cancellation UI into one collapsed `Aksi massal` workspace below the filters and before the exception queue.
- Added human-review preview details from existing `$bulkSessions`, neutral zero-candidate feedback, required-reason labeling, restrained destructive CTA, and responsive controls.
- Preserved bulk roster action, exact field/route/CSRF/confirmation contracts, authorization boundary, flash/error rendering, and EXC-UI-1/2/3 presentation.
- Verification: targeted exception test 14 tests/94 assertions; Academic regression 265 tests/1,077 assertions; view cache, PHP lint, and Pint PASS. EXC-UI-5 not started.

# 2026-09-13 — EXC-UI-5: final responsive and accessibility reconciliation
- Audited the exception page across desktop, tablet, and mobile presentation boundaries while preserving EXC-UI-1 through EXC-UI-4.
- Added minimum focus-visible/focus-within styling for the native mass-action summary and transparent date controls; verified semantic labels, alert roles, mobile queue hooks, touch actions, and no horizontal-scroll helper.
- No backend, query, route, business logic, database, or real-data changes.
- Verification: targeted exception test 14 tests/103 assertions; Academic regression 265 tests/1,086 assertions; view cache, PHP lint, and Pint PASS. Exception UI is ready to freeze pending visual review.

# 2026-09-14 — LOGIN-UI-1: modern auth shell and layout foundation
- Replaced the small admin-style login card with a centered responsive auth shell using the canonical `public/images/logo-imtaq.png` asset directly.
- Simplified copy, modernized namespaced controls, added autocomplete semantics and visible focus states, while preserving the complete authentication contract.
- No controller, route, RBAC, database, migration, seed, model, redirect, or logo-file changes.
- Verification: Auth 5 tests/37 assertions; Auth + Academic regression 270 tests/1,123 assertions; view cache, PHP lint, and Pint PASS. LOGIN-UI-2 not started.

# 2026-09-14 — LOGIN-UI-1R: auth card proportion and spacing refinement
- Refined only responsive card width/padding, logo scale, title hierarchy, and vertical rhythm for the approved login shell.
- Preserved minimal copy, canonical logo, field contracts, authentication flow, pale-green background, and mobile responsiveness.
- Verification: Auth 5 tests/37 assertions; view cache, PHP lint, and Pint PASS. LOGIN-UI-2 not started.

# 2026-09-14 — LOGIN-UI-2: form controls and password interaction
- Added a minimal vanilla JS password visibility toggle with stable data hooks, non-submit button semantics, dynamic accessible labels, and scoped autofill styling.
- Preserved the single password field, exact form payload, CSRF, login route, authentication behavior, approved copy, and responsive shell.
- Verification: Auth 5 tests/41 assertions; Auth + Academic regression 270 tests/1,127 assertions; view cache, PHP lint, and Pint PASS. LOGIN-UI-3 not started.
# 2026-09-14 — LOGIN-UI-3

- Implemented authentication error and submit feedback states in the login view only.
- Added native-submit hooks and `Memproses...` disabled state while preserving browser validation and the normal POST flow.
- Verified `LocalAuthenticationTest` (5 tests / 44 assertions) and Auth + Academic regression (270 tests / 1130 assertions), view cache, PHP lint, and Pint.
- No backend, route, database, RBAC, or authentication behavior changes.
# 2026-09-14 — LOGIN-UI-4

- Completed final responsive and accessibility reconciliation for `/login`.
- Increased the password toggle touch target to 44px; preserved all approved layout, copy, native validation, and authentication states.
- Verified LocalAuthenticationTest (5 tests / 44 assertions), Auth + Academic regression (270 tests / 1130 assertions), view cache, PHP lint, and Pint.
- No backend, route, auth logic, database, RBAC, or real-data changes.
# 2026-09-14 — DASH-UI-1

- Modernized the Academic Dashboard top hierarchy and period command bar only.
- Added dynamic current-period context and compact period wording using existing Blade variables; preserved GET contract and all downstream dashboard sections.
- Verified dashboard tests (36 tests / 139 assertions), Auth + Academic regression (270 tests / 1132 assertions), view cache, PHP lint, and Pint.
- No backend, service, route, database, RBAC, formula, or real-data changes.
# 2026-09-14 — DASH-UI-2

- Refined Academic Dashboard KPI hierarchy: physical attendance and completeness are primary; student and teacher counts are contextual.
- Applied semantic visual treatment without warning colors, thresholds, score labels, or formula changes; preserved null/zero behavior.
- Verified dashboard tests (36 tests / 143 assertions), Academic regression (265 tests / 1092 assertions), view cache, PHP lint, and Pint.
- No backend, service, route, database, RBAC, or real-data changes.
# 2026-09-14 — DASH-UI-3

- Modernized the Academic Dashboard operational status surface and replaced empty right-rail placeholders with role-aware quick actions.
- Reused existing attendance-control, monthly-report, and CSV-export routes without changing capability or export contracts.
- Verified dashboard tests (37 tests / 159 assertions), Academic regression (266 tests / 1108 assertions), view cache, PHP lint, and Pint.
- No backend, service, route, database, RBAC, KPI, or real-data changes.
# 2026-09-14 — DASH-UI-4

- Modernized `Pemantauan Kelas` into compact, scannable monitoring rows with separate resolved, missing, completeness, and progress presentation.
- Preserved canonical values, class order, null semantics, progressbar accessibility, and all frozen dashboard sections.
- Verified dashboard tests (37 tests / 160 assertions), Academic regression (266 tests / 1109 assertions), view cache, PHP lint, and Pint.
- No backend, service, route, database, RBAC, formula, or real-data changes.

# 2026-09-15 — JOINT-2B: scope-aware attendance exception queue

- Aligned exception list filtering and mass-cancellation preview with effective `ClassSessionGroup` scope, while retaining one queue item per session and the existing canonical attendance CTA.
- Added truthful joint class labels to the queue and bulk preview; single-class presentation, facets, authorization, cancellation safeguards, routes, and business facts remain unchanged.
- Added focused joint queue regressions covering unfiltered visibility, both scoped class filters, unrelated exclusion, bulk preview scope, result counts, and canonical CTA.
- Verification: `AttendanceExceptionUiTest` 15 tests/121 assertions; Academic regression 275 tests/1161 assertions; view cache, PHP lint, and Pint PASS. JOINT-2C not started.

# 2026-09-15 — JOINT-2C: scope-aware attendance review and safe navigation

- Updated completed-session review discovery and shared review exports to match effective `ClassSessionGroup` scope while preserving distinct `ClassSession` grain.
- Added truthful joint labels to the review list and neutral safe return navigation for joint attendance detail; review-route detail returns to the canonical review list.
- Audited Wali context and mutation paths; no Wali read/mutation privilege was expanded and no existing authorization method was changed.
- Verification: targeted Academic tests 50 tests/386 assertions; Academic regression 275 tests/1171 assertions; view cache, PHP lint, and Pint PASS. Monthly report remediation not started.

# 2026-09-15 — JOINT-3A: per-class dashboard and trend participant partition

- Reused the canonical joint roster breakdown to attribute joint participants by exactly one valid session-date enrollment for class-scoped metrics; ambiguous/unmapped participants are excluded rather than assigned to the anchor.
- Fixed Wali dashboard trend, operational attendance-session roster, period completion, and class attendance metrics to avoid exposing participants from another joint class. Institutional Waka/Super aggregation remains complete-snapshot and session-safe.
- Preserved attendance formulas, session counts, dashboard UI, Wali mutation authority, WAKA-2, and monthly reports.
- Verification: targeted dashboard/joint tests 39 tests/179 assertions; Academic regression 276 tests/1176 assertions; PHP lint and Pint PASS. JOINT-4 not started.

# 2026-09-18 — PHASE 2R-B1: canonical attendance semantic adapter

- Added the scoped canonical semantic boundary without changing stored attendance values or database schema.
- Normalized `LATE` to `PRESENT` with punctuality `LATE`, mapped `IZIN` to `PERMISSION`, and kept generic `EXCUSED` fail-closed as reconciliation-required.
- Made canonical present, resolved, missing, completeness, and attendance-rate formulas explicit; prevented late double counting.
- Preserved NON_ELIGIBLE outside the attendance outcome denominator and left dashboard-wide consumers for a later migration phase.
- Verification: focused semantic tests 10 tests/48 assertions; targeted Academic regression 44 tests/210 assertions; full suite 402 tests/1698 assertions; PHP lint and Pint PASS.
- No database, migration, RBAC, UI, historical data, or AI/OpenAI changes. Phase 2R-B1 complete; next phase not started.

# 2026-09-18 — PHASE 2R-B1.1: reconciliation metric invariant hardening

- Exposed the canonical transitional accounting invariant: eligible = resolved + missing + reconciliation-required.
- Added explicit tests for official rate, mixed missing/reconciliation populations, zero denominator, and source-authority compatibility.
- Audited the Phase 2R-B1 resolver change as compatibility-only; no authority policy was changed and dashboard/export consumers remain unmigrated.
- Verification: focused tests 17 tests/93 assertions; Academic regression 290 tests/1241 assertions; full suite 403 tests/1709 assertions; PHP lint and Pint PASS.
- No database, migration, RBAC, UI, historical data, or AI/OpenAI changes. Phase 2R-B1.1 complete; B2A not started.

# 2026-09-18 — PHASE 2R-B2A: canonical metrics consumer migration

- Migrated `AttendanceSemanticMetricsService` to delegate all attendance/session semantics to `CanonicalAttendanceSemanticService`.
- Preserved direct caller return keys as compatibility aliases while making attendance rate, completeness, late handling, reconciliation, missing, and non-eligible behavior canonical.
- Kept `AcademicRoleDashboardService` and `AcademicDashboardExportService` source unchanged; downstream consumer migration remains deferred.
- Verification: focused tests 18 tests/102 assertions; direct dashboard caller regression 38 tests/176 assertions; Academic regression 291 tests/1250 assertions; full suite 404 tests/1718 assertions; PHP lint and Pint PASS.
- No database, migration, RBAC, UI, historical data, source-authority policy, or AI/OpenAI changes. Phase 2R-B2A complete; B2B not started.

# 2026-09-18 — PHASE 2R-B2B: Academic dashboard canonical metrics consumer

- Migrated `AcademicRoleDashboardService` attendance composition to consume canonical values from `AttendanceSemanticMetricsService`, including canonical eligible/present/late/permission/sick/absent/resolved/missing/reconciliation metrics and compatibility aliases.
- Replaced local student attendance trend status filtering and `PRESENT + LATE` rate arithmetic with per-class canonical metrics aggregation; preserved effective joint class partitioning through the upstream canonical metrics service.
- Updated only directly related dashboard regression expectations for canonical eligible-denominator rates and zero-denominator behavior. Export source, canonical metrics source, source-authority resolver, RBAC, and session vocabulary remain unchanged.
- Verification: focused dashboard 38 tests/177 assertions; B2A metrics 6 tests/40 assertions; canonical contract 11 tests/59 assertions; joint/dashboard 39 tests/180 assertions; Academic regression 291 tests/1251 assertions; full suite 404 tests/1719 assertions; view cache, PHP lint, and Pint PASS.
- No database, migration, historical rewrite, RBAC, UI, source-authority policy, or AI/OpenAI changes. Phase 2R-B2B complete; B2C not started.

# 2026-09-18 — CIBS-RB: canonical implementation baseline synchronization

- Reconciled current pointers against the existing Academic source, tests, task queue, accepted recovery manifests, SOC-RDG evidence, and SOC-MDG policy register.
- Created `codex/GOVERNANCE/CANONICAL_IMPLEMENTATION_BASELINE_v1.0.md`; preserved historical/recovery artifacts and SOC policy register v1.0 unchanged.
- Synchronized only current-pointer documents; no application source, tests, database, migrations, seed/import, RBAC, session occurrence behavior, or AI/OpenAI changes.
- Verified POST-B2C application source continuity: 0 mismatches. Academic queue counts: 67 total, 65 DONE, 1 BLOCKED_POLICY, 1 NOT_STARTED, 0 other.

# 2026-09-19 — SOC-I1C: canonical session occurrence workflow and RBAC integration

- Added a single default-OFF occurrence feature gate, scoped Wali/Waka authorization, gated occurrence routes/UI/history, and transactional integration with existing cancellation and reschedule services.
- Preserved append-only SOC-I1B writes, legacy attendance behavior while the gate is OFF, teacher-participation substitution facts, joint-session safeguards, and no denominator or historical-data changes.
- Added focused gate/joint authorization regressions. Verification: focused 81 tests/367 assertions; Academic 325/1314; full 438/1782; view cache, PHP lint, and Pint PASS.
- Pilot PostgreSQL was read-only: 42 applied migrations, 0 pending, 0 occurrence versions, 0 effective pointers. Recovery checkpoint: `recovery/soc-i1c/SOC-I1C_20260919-160000/`.

# 2026-09-19 — SOC-I1D: canonical denominator and explicit cutover semantics

- Added `SessionOccurrenceCutover` as the single gate/timestamp/regime authority using `ClassSession.planned_start_at`; default gate remains OFF and cutover timestamp remains null.
- Kept legacy `COMPLETED` population for pre-cutover history while requiring effective canonical `HELD` for post-cutover opportunities. Prohibited legacy fallback and exposed missing post-cutover occurrence as a DQ fact.
- Added boundary, fail-closed, post-cutover population, incomplete HELD, partial HELD storage, and no-fallback regressions. Verification: focused 103/482; Academic 329/1327; full 442/1795; lint, Pint, and view cache PASS.
- Pilot PostgreSQL was read-only: 42 applied migrations, 0 pending, 0 occurrence versions, 0 effective pointers. Recovery checkpoint: `recovery/soc-i1d/SOC-I1D_20260919-160000/`.

# 2026-09-19 — SOC-I1E: explicit pilot activation and final Session Occurrence closeout

- Created and validated a pre-activation PostgreSQL logical backup and schema dump; classified 1,270 sessions with zero NULL occurrence datetimes.
- Activated only the existing occurrence runtime keys at `2026-09-20T00:00:00+07:00 Asia/Jakarta`; no source, migration, seed/import, historical rewrite, or synthetic occurrence was created.
- Post-activation pilot verification remains 0 occurrence versions / 0 effective pointers / 0 unexpected business writes. First real occurrence is pending operational use.
- Verification: focused 103/482; Academic 329/1327; full 442/1795; PHP lint, Pint, and view cache PASS. AI Academic Assistant gate is open for read-only MVP work; NON_ELIGIBLE and source-authority precedence remain closed/not activated.
- Recovery checkpoint: `recovery/soc-i1e/SOC-I1E_20260919-160000/`.

# 2026-09-19 — AI-A0: canonical AI readiness rebase and MVP scope freeze

- Reconciled the accepted SOC-I1E Academic baseline with current source and read-only PostgreSQL migration evidence; 42 repository migrations and 42 database migration rows are `Ran`.
- Identified canonical student/session/attendance sources, Waka authorization, effective joint-class partitioning, cutover semantics, and append-only audit infrastructure.
- Classified existing AI-ACADEMIC-2B/2C artifacts as reusable semantic/read-only evidence or superseded planning where open governance prevents certification authority; no AI runtime or provider implementation exists.
- Created four AI-A0 governance artifacts and froze exactly five typed read tools, structured evidence, privacy/security/audit boundaries, 15 evaluation cases, and the exact AI-A1 plan.
- Verification: reconnaissance and read-only migration status only. No application source, route, runtime config, migration, database write, attendance semantic, RBAC, or AI runtime change.
- AI-A0 closeout: `PASS`; AI-A1 readiness: `READY_FOR_IMPLEMENTATION`; next atomic task: `RETURN_TO_CHATGPT_FOR_AI_A0_AUDIT`.

# 2026-09-19 — AI-A1: typed Academic read tools

- Implemented the provider-agnostic Academic AI tool layer with server-owned context, centralized Waka authorization, deterministic result/evidence envelope, and exactly five registered read-only tools.
- Reused canonical student/session/attendance models and semantic services; no local denominator/rate calculation, no joint-session double count, no ranking, and no write capability.
- Added focused contract, identity, summary, validation, and bounded-output tests. AI-A1 focused 11/28, Academic 340/1355, full 453/1823; lint, Pint, and view cache PASS.
- Created durable recovery checkpoint `recovery/ai-a1/AI-A1_20260919-205329/`; intentional and baseline dependency hash manifests verified.
- No migration, schema, pilot PostgreSQL write, OpenAI/provider, HTTP endpoint, UI, RBAC, or attendance business semantic change.
- AI-A1 closeout: `PASS`; AI-A2 provider/runtime not started; next atomic task: `RETURN_TO_CHATGPT_FOR_AI_A1_AUDIT`.

# 2026-09-20 — AI-A2: OpenAI Responses runtime and typed orchestration

- Implemented a provider-agnostic Academic AI model boundary, OpenAI Responses adapter over Laravel HTTP Client, configurable server-side runtime settings, strict schemas for exactly five AI-A1 tools, and a bounded sequential tool-call loop.
- Added exact call-id handback, unknown-tool/malformed-argument/provider-failure handling, ambiguous student continuation guard, request correlation/provider request tracing, existing AuditLogger metadata, and hashed-question privacy handling.
- Added deterministic provider-fake, transport contract, orchestration, security, ambiguity, and loop-limit tests. AI-A2 focused 18/57, Academic 347/1384, full 460/1852; PHP lint, Pint, and view cache PASS.
- PostgreSQL migration status read-only: 42 migration files / 42 rows, all `Ran`, 0 `Pending`. No pilot business write, migration, seed/import, route, UI, or attendance semantic change.
- Live OpenAI smoke was not run; no credential was exposed. Recovery checkpoint: `recovery/ai-a2/AI-A2_20260920-085400/`.
- AI-A2 closeout: `PASS`; AI-A3 HTTP exposure remains not started; next atomic task: `RETURN_TO_CHATGPT_FOR_AI_A2_AUDIT`.

# 2026-09-20 — AI-A3: authenticated Waka AI query endpoint

- Added one POST-only authenticated `/academic/ai-assistant/query` route behind the single default-OFF `academic.ai.assistant_enabled` gate.
- Added server-owned Waka authorization delegation, strict question validation, unknown control-field rejection, authenticated rate limiting, safe no-store JSON envelope, sanitized provider failure mapping, and correlation continuity to AI-A2.
- Corrected strict function-schema optional/nullability contract without changing tool semantics. Added HTTP, middleware, gate, rate-limit, identity, privacy, failure, and no-write regressions.
- Verification: AI-A3/AI-A2/AI-A1 27/91; Academic 356/1418; full 469/1886; PHP lint, Pint, view cache PASS. SQLite in-memory tests only; PostgreSQL 42/42 Ran and 0 Pending read-only.
- Pilot AI remains OFF; no live OpenAI request or student data sent. Recovery checkpoint: `recovery/ai-a3/AI-A3_20260920-092402/`.
- AI-A3 closeout: `PASS`; AI-A4 UI not started; next atomic task: `RETURN_TO_CHATGPT_FOR_AI_A3_AUDIT`.

# 2026-09-20 — AI-A5: security, grounding, evidence, cost, and provider readiness hardening

- Added server-owned output-token limits, HMAC question audit digest, deterministic instruction version, grounding status, successful-tool evidence, round count, warning continuity, and bounded usage metadata.
- Refused ungrounded factual model text while preserving safe clarification/limitation/refusal; strengthened prompt-injection instructions and preserved exact five read-only tools, `store=false`, bounded sequential orchestration, and disabled feature gate.
- Added focused grounding, privacy, transport-cap, and security regressions. Verification: AI-A5 focused 29/102; Academic 360/1445; full 473/1913; view cache, PHP lint, and Pint PASS.
- Provider configuration inspection was boolean-only: API key absent and provider disabled; live synthetic OpenAI smoke not run. No real student data, pilot activation, database write, migration, seed/import, or academic business write.
- Created governance baseline `codex/GOVERNANCE/AI_ACADEMIC_MVP_HARDENING_BASELINE_v1.0.md`, manifest `codex/CHANGE_MANIFESTS/AI-A5-2026-09-20.md`, and recovery checkpoint `recovery/ai-a5/AI-A5_20260920-110000/`.
- AI-A5 closeout: `PASS`; next atomic task: `RETURN_TO_CHATGPT_FOR_AI_A5_AUDIT`.

# 2026-09-20 — AI-A5K: secure provider configuration and model management

- Implemented minimum database-managed OpenAI provider infrastructure: encrypted/hidden credentials, versioned configurations, active pointer, Super Admin-only UI/routes, server-side model discovery, explicit verify-before-activate, transactional activation, safe revoke, and runtime kill switch.
- Updated `OpenAiResponsesProvider` to resolve only the active database configuration; legacy env key/model values are compatibility-only with no silent runtime fallback. Public AI feature remains OFF and no credential/configuration was seeded.
- Added AI-A5K provider security/RBAC/lifecycle regressions. Verification: AI-A5K focused 33/113; Academic 364/1456; full 477/1924; view cache, PHP lint, and Pint PASS.
- Fresh PostgreSQL custom backup and `pg_restore --list` passed; PostgreSQL migration dry-run passed. Pilot remains 42 applied with the AI-A5K migration pending. Disposable PostgreSQL migration verification was blocked by the execution approval runner; pilot migration was not applied.
- Created `codex/GOVERNANCE/AI_PROVIDER_CONFIGURATION_GOVERNANCE_v1.0.md`, manifest `codex/CHANGE_MANIFESTS/AI-A5K-2026-09-20.md`, and recovery `recovery/ai-a5k/AI-A5K_20260920-120000/`.
- AI-A5K closeout: `BLOCKED / SAFE CHECKPOINT`; next atomic task: `RETURN_TO_CHATGPT_FOR_AI_A5K_AUDIT`.
## 2026-09-20 — AI-A5K-IR1 target-mismatch incident closeout

- Classified the unexpected pilot application of the AI-A5K migration as `DEPLOYMENT_TARGET_CONTROL_INCIDENT`; root cause was cached Laravel database configuration ignoring shell-only database overrides.
- Read-only pilot inventory confirmed `imtaq` on PostgreSQL 18.6, 43 applied migrations, three AI-provider tables, zero credentials/configurations/active pointers, and the public AI gate OFF.
- Created and validated a fresh post-incident pilot backup; compared 72 non-AI tables and data-only dumps against the accepted pre-A5K baseline with no Academic business-data mutation.
- Restored the baseline to an isolated loopback PostgreSQL 18.6 cluster on port 55433, passed direct and Laravel-resolved target guards, applied the AI-A5K migration, passed FK/unique/RESTRICT checks, schema equivalence, rollback/reapply, and regression suites.
- Added `migrate:guarded` plus `DatabaseTargetGuard` and mismatch regression tests so future migration writes require actual Laravel and PostgreSQL identity agreement.
- Closeout: `AI_A5K_IR1=CLOSED/ACCEPTED`; no pilot rollback, credential, provider activation, OpenAI request, or Academic business write.
# 2026-09-24 — AI-PROVIDER-R1-A: provider verification and lifecycle integrity

- Replaced configuration verification by HTTP 2xx with a synthetic two-request function roundtrip: synthetic resolver token, `NOT_FOUND`, matching call ID output, and deterministic completion marker.
- Enforced DRAFT-only verification and rejected ACTIVE, SUPERSEDED, and revoked-credential verification without changing failed configuration status.
- Added safe provider failure taxonomy and append-only failed verification audit metadata without raw secret/body payloads.
- Added R1-A focused tests and ran AI 44/176, Academic 375/1519, and full 488/1987 regressions; PHP lint, Pint, and view cache PASS.
- No migration, database write, activation, live OpenAI request, real credential, or real Academic data use. Recovery checkpoint: `recovery/ai-provider-r1a/AI-PROVIDER-R1A_20260924-185717/`.
- R1-A closeout: `PASS`; Gate B remains `BLOCKED_PENDING_R1B`; next atomic task: `RETURN_TO_CHATGPT_FOR_R1A_AUDIT`.
# 2026-09-24 — AI-PROVIDER-R1-B: credential/model binding and duplicate-DRAFT integrity

- Added server-side model re-discovery at configuration submit using only the selected credential; arbitrary/stale model strings are rejected.
- Added PostgreSQL transaction advisory locking plus equality-key duplicate guard for equivalent open DRAFTs; no migration or constraint was created.
- Added stale discovery protection with AbortController/generation checks and safe DRAFT metadata for existing duplicates.
- Added R1-B focused tests and ran AI 48/194, Academic 379/1537, and full 492/2005 regressions; PHP lint, Pint, and view cache PASS.
- No existing DRAFT mutation, migration, database write, activation, live OpenAI request, real credential, or real Academic data use. Recovery checkpoint: `recovery/ai-provider-r1b/AI-PROVIDER-R1B_20260924-200821/`.
- R1-B closeout: `PASS`; Gate B `READY_FOR_CONTROLLED_PROVIDER_VERIFICATION`; Public AI remains OFF; next atomic task: `RETURN_TO_CHATGPT_FOR_R1B_AUDIT`.

# 2026-09-25 — AI-PROVIDER-READINESS-E1: activation-critical evidence closure

- Closed the five readiness traceability gaps with test-only evidence: disposable PostgreSQL advisory-lock concurrency, canonical audit entities, explicit WAKA_AKADEMIK backend denial, executable in-flight submit behavior, and audit secret/header absence.
- Disposable PostgreSQL identity guards passed for `imtaq_e1_disposable`; two concurrent workers produced one DRAFT and one canonical duplicate rejection; final equivalent-DRAFT count was 1. The disposable cluster was stopped and removed.
- Verification: AI/Provider 59/261; Academic 390/1604; full 503/2072; frontend Node test 2/2; PHP lint, Pint, and view cache PASS.
- No production behavior, migration, schema, pilot database, existing DRAFT, credential/configuration state, provider runtime, public AI gate, live OpenAI request, or real data changed.
- E1 closeout: `PASS`; Gate B `READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`; next atomic task: `RETURN_TO_CHATGPT_FOR_READINESS_E1_AUDIT`.

# 2026-09-25 — AI-PROVIDER-UX-S1: simplified Super Admin AI Provider settings

- Reorganized the existing Blade page into a clear primary workflow for API Key selection, server-discovered Model AI selection, and explicit save-and-test.
- Added collapsed `Pengaturan Lanjutan` for runtime controls and historical credential/configuration metadata, without changing backend actions or lifecycle state.
- Preserved R1-A through R1-D/E1 security controls, responsive/accessibility behavior, stale-discovery protection, in-flight submit protection, RBAC, and public-AI read-only status.
- Verification: provider 25/144, Academic 391/1613, full 504/2081, JavaScript 2/2; view cache, Pint test, and PHP lint PASS.
- No migration, database write, credential/configuration mutation, existing DRAFT mutation, provider verification/activation, OpenAI request, or Academic business-data change.
- Recovery: `recovery/ai-provider-ux-s1/AI-PROVIDER-UX-S1_20260925-070000/`; manifest: `codex/CHANGE_MANIFESTS/AI-PROVIDER-UX-S1-2026-09-25.md`.
- UXS1 closeout: `PASS`; Gate B after UXS1 `READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`; next atomic task: `RETURN_TO_CHATGPT_FOR_UXS1_AUDIT`.

# 2026-09-25 — AI-PROVIDER-UX-S1.1: modern minimal AI Provider settings refinement

- Refined the existing Super Admin AI Provider Blade into one primary read-mode settings card with on-demand edit mode, canonical lifecycle labels, read-only Public Academic AI status, and collapsed advanced settings.
- Kept existing routes, forms, server-side model discovery, lifecycle/RBAC/security controls, duplicate-submit protection, historical DRAFT safety, and backend behavior unchanged.
- Verification: provider focused 27/159, Academic 393/1628, JavaScript 2/2, view cache, Pint, and PHP lint PASS. Full suite command was rerun without emitted output from the local test wrapper; the last accepted full baseline remains 504/2081 and the Academic suite covers the changed feature.
- No migration, database write, credential/configuration mutation, existing DRAFT mutation, provider/public AI activation, live OpenAI request, or Academic business-data change.
- Recovery: `recovery/ai-provider-ux-s1/AI-PROVIDER-UX-S1.1_20260925-080000/`; manifest: `codex/CHANGE_MANIFESTS/AI-PROVIDER-UX-S1.1-2026-09-25.md`.
- UXS1.1 closeout: `PASS`; Gate B after UXS1.1 `READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`; next atomic task: `RETURN_TO_CHATGPT_FOR_UXS11_AUDIT`.
- Follow-up visual refinement: added spacing before API Key/configuration history lists; focused provider test, view cache, Pint, and Blade lint PASS. No behavior or data change.

# 2026-09-26 — AI-PROVIDER-DIAG-D3: safe provider error diagnostics and contract evidence

- Added a bounded in-memory provider error parser that persists only whitelisted `error.type`, `error.code`, and `error.param` fields; raw provider bodies, messages, headers, and secrets remain excluded.
- Added executable exact Responses request-envelope and recursive strict-schema tests, plus safe audit metadata assertions.
- Verification: provider focused 29/226, Academic 395/1695, full 508/2163; PHP lint, Pint, and view cache PASS.
- No live OpenAI request, real key use, provider/DRAFT/active-pointer mutation, migration, schema change, Academic data external processing, or public AI activation.
- Recovery: `recovery/ai-provider-diag-d3/AI-PROVIDER-DIAG-D3_20260926-120000/`; manifest: `codex/CHANGE_MANIFESTS/AI-PROVIDER-DIAG-D3-2026-09-26.md`.
- D3 closeout: `PASS`; `CONTROLLED_DIAGNOSTIC_RETRY_READINESS=READY`; next atomic task: `RETURN_TO_CHATGPT_FOR_DIAG_D3_AUDIT`.

# 2026-09-26 — AI-PROVIDER-INPUT-P1: continuation input and error taxonomy patch

- Wrapped the synthetic `function_call_output` continuation input as a JSON list of one Responses input item; initial input remains a string.
- Added exact `INVALID_INPUT_TYPE` mapping for the whitelisted `invalid_request_error / invalid_type / input` tuple while preserving safe fallback categories.
- Verification: P1/provider focused 30/237, AI provider 65/363, Academic 396/1706, full 509/2174; PHP lint, Pint, and view cache PASS.
- No live OpenAI request, real key, provider state mutation, migration, schema change, active-pointer change, runtime toggle, or public AI activation.
- Recovery: `recovery/ai-provider-input-p1/AI-PROVIDER-INPUT-P1_20260926-130000/`; manifest: `codex/CHANGE_MANIFESTS/AI-PROVIDER-INPUT-P1-2026-09-26.md`.
- P1 closeout: `PASS`; known continuation defect and taxonomy fixed; initial live input rejection root cause remains unresolved; next atomic task: `RETURN_TO_CHATGPT_FOR_INPUT_P1_AUDIT`.

# 2026-09-26 — AI-PROVIDER-INPUT-P2: deterministic verification input compatibility

- Changed only synthetic verification initial input to a one-item Responses message list and forced verification-only `resolve_student`; ordinary Academic runtime remains `tool_choice=auto`.
- Preserved five canonical strict tools, synthetic `NOT_FOUND`, P1 continuation list, D3 safe diagnostics, and `INVALID_INPUT_TYPE` taxonomy.
- Verification: provider/P2 30/243, AI provider 65/369, Academic 396/1712, full 509/2180; PHP lint, Pint, and view cache PASS.
- No live OpenAI request, real key, provider state mutation, migration, schema change, active-pointer change, runtime toggle, or Public AI activation.
- Recovery: `recovery/ai-provider-input-p2/AI-PROVIDER-INPUT-P2_20260926-140000/`; manifest: `codex/CHANGE_MANIFESTS/AI-PROVIDER-INPUT-P2-2026-09-26.md`.
- P2 closeout: `PASS`; controlled verification retry readiness `READY`; next atomic task: `RETURN_TO_CHATGPT_FOR_INPUT_P2_AUDIT`.

# 2026-09-26 — AI-PROVIDER-CONTEXT-P3: stateless verification continuation replay

- Rebuilt verification continuation as ordered stateless replay: initial synthetic input, all first response output items, then matching function output.
- Removed verification `previous_response_id`; continuation uses `store=false` and `tool_choice=none`; initial forced resolver and ordinary runtime `auto` remain unchanged.
- Verification: provider/P3 30/254, AI provider 65/380, Academic 396/1723, full 509/2191; PHP lint, Pint, and view cache PASS.
- No live OpenAI request, real key, provider state mutation, migration, schema change, active-pointer change, runtime toggle, or Public AI activation.
- Recovery: `recovery/ai-provider-context-p3/AI-PROVIDER-CONTEXT-P3_20260926-150000/`; manifest: `codex/CHANGE_MANIFESTS/AI-PROVIDER-CONTEXT-P3-2026-09-26.md`.
- P3 closeout: `PASS`; controlled full verification retry readiness `READY`; next atomic task: `RETURN_TO_CHATGPT_FOR_CONTEXT_P3_AUDIT`.

# 2026-10-04 — Academic Web Grade Workflow G2

- Implemented exact assigned-subject-teacher DRAFT entry through one atomic
  batch service with server-owned assignment/Staff provenance, optimistic
  concurrency, missing-versus-zero semantics, and no-op suppression.
- Hardened the canonical entry service so CHECKED/LOCKED normal saves fail
  closed inside row locking; rejected writes create no audit or version change.
- Added one POST batch-DRAFT route, editable teacher UI, read-only Wali/Waka and
  protected-state rendering, plus focused authorization/state/atomicity tests.
- Local DB execution stopped safely because `.env` targets PILOT `imtaq`.
  Disposable PostgreSQL CI run `37200924044` passed on executable HEAD
  `b3878d9f02fbbde06dc9e23b2562e8ad8eaacd88`: 15 passed, 558 warnings,
  2359 assertions, 0 failed.
- No migration, PILOT database write/seed, G3 work, AI/provider mutation, or
  Public Academic AI activation. Decision: `GRADE_G2_IMPLEMENTED_PASS`; next:
  `RETURN_TO_CHATGPT_FOR_ACADEMIC_WEB_GRADE_G2_AUDIT`.
