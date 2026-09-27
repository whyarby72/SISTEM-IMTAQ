# IMTAQ CORE ENGINE
## Base Engine Sistem IMTAQ — Source Baseline Project
**Versi:** 1.0  
**Tanggal Baseline:** 23 Agustus 2026  
**Status:** Dokumen Induk / Source of Reference  
**Tujuan:** Menjadi sumber utama untuk pengembangan sistem manajemen, database, reporting, Excel/BI, web application, automasi, dan AI IMTAQ Isy Karima.

---

# 0. CARA MENGGUNAKAN FILE INI

Dokumen ini adalah **base engine konseptual dan teknis** untuk seluruh pengembangan SISTEM IMTAQ.

Setiap pekerjaan lanjutan—misalnya:
- membuat database santri;
- merancang Excel;
- membuat dashboard;
- membuat form input;
- membuat laporan wali santri;
- membuat aplikasi web;
- membuat API;
- membuat prompt AI;
- membuat early warning;
- membuat laporan Idaroh/Yayasan;
- menyusun SOP input dan validasi data;
- membuat modul Tahfizh, Akademik, Kesantrian, atau Kepengasuhan;

harus terlebih dahulu merujuk kepada prinsip dan struktur dalam dokumen ini.

Jika ada rancangan baru yang bertentangan dengan dokumen ini, lakukan:
1. identifikasi konflik;
2. analisis dampak;
3. usulkan revisi;
4. buat nomor versi baru;
5. jangan menimpa keputusan lama tanpa dokumentasi perubahan.

Dokumen ini bukan spesifikasi final yang kaku. Ia adalah **living architecture** yang boleh dikembangkan secara bertahap, tetapi perubahan harus terkontrol.

---

# 1. IDENTITAS SISTEM

## 1.1 Nama Sistem

**IMTAQ CORE ENGINE**

Nama konseptual lengkap:

> **Integrated Student Development, Management & Reporting System**

Nama alternatif internal:

> **IMTAQ Student Intelligence & Reporting System (ISIRS)**

## 1.2 Definisi

IMTAQ CORE ENGINE adalah kerangka manajemen data dan informasi terintegrasi yang menghubungkan seluruh proses perkembangan santri—Tahfizh, Akademik, Kesantrian, Ruhiyah, Kepengasuhan, Bahasa, kegiatan, administrasi, dan layanan pendukung—melalui satu identitas santri, standar data, mekanisme validasi, analytics, reporting, serta workflow pengambilan keputusan bertingkat dari musyrif hingga pimpinan, Idaroh pusat, Yayasan, dan wali santri.

## 1.3 Filosofi Inti

> **Input sekali → validasi jelas → simpan terstruktur → analisis otomatis → distribusi sesuai kewenangan → tindak lanjut terukur.**

## 1.4 North Star

Sistem bukan dibangun untuk “menghasilkan laporan”.

Sistem dibangun untuk:

> **merekam proses pendidikan, menjaga kualitas data, membantu pembinaan, mempercepat pengambilan keputusan, dan menyampaikan perkembangan santri kepada stakeholder secara akurat, proporsional, dan bermanfaat.**

## 1.5 Formula Utama

> **ONE STUDENT → ONE ID → ONE HISTORY → MANY ACTIVITIES → ONE SOURCE OF TRUTH → MANY REPORTS**

---

# 2. KONTEKS OPERASIONAL IMTAQ

Struktur operasional IMTAQ mencakup sekurang-kurangnya:

1. Akademik.
2. Ketahfizhan.
3. Kesantrian.
4. Kepengasuhan asrama.
5. Halaqah Al-Qur'an.
6. Program Bahasa Arab.
7. Tarbiyah ruhiyah.
8. Ibadah yaumiyah.
9. Qiyamul-lail.
10. Adab dan akhlak.
11. Kegiatan fisik/jasadiyah.
12. SAPALA dan kegiatan alam.
13. Muhadharah/dakwah.
14. Kegiatan ekstrakurikuler.
15. Administrasi santri.
16. Perizinan dan kunjungan wali.
17. Kesehatan.
18. Komunikasi wali santri.
19. Pelaporan kepada Idaroh/pimpinan.
20. Pelaporan kepada Yayasan.

Pola kepengasuhan IMTAQ bersifat intensif. Karena itu sistem harus mendukung pengelolaan santri pada level:
- individu;
- musyrif;
- kamar;
- halaqah;
- kelas;
- angkatan;
- bidang;
- unit;
- lembaga.

---

# 3. PRINSIP KONSTITUSIONAL SISTEM

Seluruh modul wajib mengikuti prinsip berikut.

## P-01 — Single Source of Truth

Tidak boleh ada beberapa versi data resmi yang saling berbeda.

Contoh:
- satu master nama santri;
- satu status aktif;
- satu assignment kelas;
- satu assignment halaqah;
- satu assignment kamar.

## P-02 — One Student ID

Setiap santri mempunyai ID unik permanen.

Nama santri tidak boleh menjadi primary identifier.

## P-03 — Record Once, Use Many

Data dicatat satu kali dan dapat digunakan oleh:
- laporan wali;
- dashboard musyrif;
- dashboard bidang;
- laporan kepala unit;
- Idaroh;
- Yayasan;
- analitik;
- AI.

## P-04 — Transaction Before Report

Sistem harus menyimpan kejadian/transaksi terlebih dahulu.

Contoh yang benar:

`STU-001 → ZIYADAH → 23-08-2026 → Al-Baqarah 120–130 → PASS`

Bukan hanya:

`Tahfizh Agustus = 87%`

## P-05 — Validation Before Publication

Data mentah tidak otomatis menjadi data resmi.

Gunakan lifecycle:

`DRAFT → SUBMITTED → VALIDATED → LOCKED → PUBLISHED`

## P-06 — Clear Data Ownership

Setiap jenis data harus mempunyai:
- creator/inputter;
- validator;
- owner;
- approver jika diperlukan.

## P-07 — Auditability

Perubahan data penting harus tercatat:
- siapa;
- kapan;
- nilai sebelum;
- nilai sesudah;
- alasan;
- approval.

## P-08 — Role-Based Information

Tidak semua orang melihat semua data.

Gunakan prinsip:
- least privilege;
- need to know;
- separation of duties.

## P-09 — AI Is Not Source of Truth

AI boleh:
- meringkas;
- mengklasifikasikan;
- menyusun draft;
- menemukan pola;
- membantu query.

AI tidak boleh:
- menciptakan fakta;
- mengubah data resmi tanpa approval;
- mengambil keputusan disipliner final;
- menentukan kondisi batin/iman;
- memberi label karakter secara otomatis.

## P-10 — Education Before Technology

Tujuan tarbiyah dan pendidikan menentukan bentuk sistem, bukan sebaliknya.

## P-11 — Observable Before Scorable

Hanya perilaku/hasil yang dapat diamati yang boleh dijadikan data terukur.

Hindari membuat skor seperti:
- “Skor Iman”;
- “Skor Ikhlas”;
- “Nilai Kesalehan”.

Gunakan indikator yang dapat diamati:
- kehadiran jamaah;
- keterlibatan halaqah;
- penyelesaian target;
- kehadiran program;
- tindak lanjut pembinaan.

## P-12 — Actionable KPI

Setiap KPI harus menjawab:

> “Keputusan apa yang berubah jika angka ini naik atau turun?”

Jika tidak ada keputusan yang dapat diambil, KPI tersebut perlu dipertanyakan.

---

# 4. VIRTUAL EXPERT BOARD — REFERENCE FRAMEWORK

Tokoh di bawah ini bukan pihak yang secara nyata terlibat di IMTAQ. Mereka adalah **reference framework** untuk mengambil metodologi dan pola pikir pada domain tertentu.

## 4.1 Manajemen Pendidikan Islam / Pesantren

### Prof. Hasan Baharun
Peran referensi:
- manajemen pendidikan Islam;
- transformasi digital pesantren;
- governance lembaga;
- pengembangan layanan pendidikan Islam.

Digunakan ketika menjawab:
- apakah sistem sesuai karakter pesantren?
- apakah proses digital menguatkan atau melemahkan tarbiyah?
- bagaimana governance lembaga dibentuk?

## 4.2 Educational Data Mining

### Prof. Ryan S. Baker
Peran referensi:
- student analytics;
- educational data mining;
- behavior analytics;
- predictive educational analysis.

Digunakan ketika menjawab:
- indikator apa yang bermakna?
- bagaimana data santri dianalisis?
- bagaimana trend dan pola perkembangan dibaca?

## 4.3 Learning Analytics

### Prof. Dragan Gašević
Peran referensi:
- learning analytics;
- AI dalam pendidikan;
- hubungan analytics dan intervensi pembelajaran.

Digunakan ketika menjawab:
- bagaimana data diterjemahkan menjadi intervensi?
- bagaimana trend dipakai untuk pembinaan?

## 4.4 AI in Education

### Prof. Rose Luckin
Peran referensi:
- AI governance pendidikan;
- human-centered AI;
- AI sebagai alat bantu pendidikan.

Digunakan ketika menjawab:
- apakah AI memberi manfaat pedagogis?
- apakah AI menggantikan fungsi pendidik?
- bagaimana menjaga akuntabilitas manusia?

## 4.5 Modern Data Engineering

### Joe Reis
Peran referensi:
- data lifecycle;
- data architecture;
- pipelines;
- modeling;
- data platform;
- data ownership.

Digunakan ketika merancang:
- sumber data;
- ingestion;
- storage;
- transformation;
- serving;
- lifecycle data.

## 4.6 Data Contracts

### Chad Sanderson
Peran referensi:
- schema contract;
- data quality rule;
- ownership;
- versioning;
- producer-consumer agreement.

Digunakan untuk:
- menentukan field wajib;
- memastikan data konsisten;
- membuat kontrak antar modul.

## 4.7 Data Observability

### Barr Moses
Peran referensi:
- data quality monitoring;
- observability;
- anomaly detection;
- reliability.

Digunakan untuk:
- mendeteksi data hilang;
- duplicate;
- data telat;
- abnormal value;
- kualitas pipeline.

## 4.8 Excel / Power Query

### Leila Gharani
Peran referensi:
- Excel Tables;
- Power Query;
- Power Pivot;
- dashboard;
- automation;
- prototype MIS.

Digunakan pada fase:
- MVP;
- proof of concept;
- migrasi data;
- reporting operasional.

## 4.9 BI / Semantic Model

### Marco Russo
Peran referensi:
- semantic model;
- DAX;
- Power BI;
- reusable measures.

### Alberto Ferrari
Peran referensi:
- advanced DAX;
- analytical model;
- performance;
- calculation engine.

## 4.10 Dashboard & Visualization

### Purna “Chandoo” Duggirala
Peran referensi:
- executive dashboard;
- visual reporting;
- information hierarchy;
- readability.

## 4.11 Generative AI Adoption

### Ethan Mollick
Peran referensi:
- penggunaan GenAI di organisasi;
- workflow manusia + AI;
- evaluasi penggunaan AI.

## 4.12 AI Engineering

### Andrew Ng
Peran referensi:
- agentic workflow;
- tool use;
- planning;
- evaluation;
- AI system architecture.

## 4.13 LLM Application Engineering

### Isa Fulford
Peran referensi:
- LLM integration;
- structured outputs;
- retrieval;
- agent applications;
- report generation.

## 4.14 Backend / Application Engineering

### Taylor Otwell
Peran referensi:
- Laravel;
- backend architecture;
- authentication;
- authorization;
- API;
- queues;
- application workflow.

## 4.15 Modern Web / AI-Native Frontend

### Guillermo Rauch
Peran referensi:
- modern frontend;
- realtime interface;
- AI-native UX;
- deployment architecture.

## 4.16 Regulatory / Islamic Education Ecosystem

### Prof. Amien Suyitno
Peran referensi:
- alignment dengan ekosistem Pendidikan Islam Indonesia;
- integrasi kebutuhan data Kementerian Agama;
- digitalisasi pendidikan Islam.

---

# 5. DOMAIN PERKEMBANGAN SANTRI

Base Engine menggunakan minimal delapan domain.

## D-01 — Tahfizh

Subdomain:
- tahsin;
- pra-tahfizh;
- ziyadah;
- murajaah;
- juziyyah;
- tasmi';
- khatmul Qur'an;
- Ujian Akhir Tahfizh;
- sanad/qira'ah;
- kehadiran halaqah;
- konsistensi;
- target vs realisasi.

## D-02 — Akademik

Subdomain:
- mata pelajaran;
- kehadiran;
- tugas;
- quiz;
- ujian;
- nilai;
- remedial;
- kompetensi;
- trend akademik.

## D-03 — Ruhiyah / Ibadah Terobservasi

Subdomain:
- shalat berjamaah;
- qiyamul-lail;
- keterlibatan program tarbiyah;
- ibadah yaumiyah yang memang dikelola program.

Catatan:
Jangan menilai kualitas batin santri.

## D-04 — Adab & Kedisiplinan

Subdomain:
- kepatuhan aturan;
- ketepatan waktu;
- pelanggaran;
- pembinaan;
- restorative action;
- follow-up;
- penyelesaian kasus.

## D-05 — Bahasa

Subdomain:
- Bahasa Arab aktif;
- mufradat;
- praktik komunikasi;
- evaluasi;
- muhadharah;
- kemampuan lisan.

## D-06 — Kepengasuhan

Subdomain:
- mentoring;
- catatan musyrif;
- target personal;
- follow-up;
- pendampingan kamar;
- perkembangan sosial;
- keteraturan kegiatan.

## D-07 — Kegiatan & Kompetensi

Subdomain:
- SAPALA;
- olahraga;
- berkuda;
- memanah;
- berenang;
- beladiri;
- dakwah;
- muhadharah;
- organisasi;
- amal jama'i;
- kegiatan ekstrakurikuler.

## D-08 — Administratif & Layanan

Subdomain:
- perizinan;
- kunjungan;
- sakit;
- perpindahan kamar;
- status aktif;
- dokumen;
- komunikasi wali;
- layanan administrasi.

---

# 6. MASTER DATA ARCHITECTURE

## 6.1 Master Student

Table: `students`

Minimum fields:

- student_id
- nis_internal
- nisn jika ada
- full_name
- arabic_name
- nickname
- gender
- birth_place
- birth_date
- entry_year
- cohort_id
- class_id_current
- halaqah_id_current
- room_id_current
- status
- photo_url
- created_at
- updated_at

## 6.2 Master Guardian

Table: `guardians`

Fields:
- guardian_id
- full_name
- relationship
- phone_primary
- phone_secondary
- email
- address
- communication_priority
- active_status

Bridge:
`student_guardians`

## 6.3 Master Staff

Table: `staff`

Fields:
- staff_id
- full_name
- role_title
- department_id
- employment_status
- system_user_id
- active_from
- active_until

## 6.4 Master Academic Year

Table:
`academic_years`

Fields:
- academic_year_id
- label
- start_date
- end_date
- active_status

## 6.5 Master Class

Table:
`classes`

Fields:
- class_id
- academic_year_id
- class_name
- level
- homeroom_staff_id
- active_status

## 6.6 Master Halaqah

Table:
`halaqahs`

Fields:
- halaqah_id
- academic_year_id
- halaqah_name
- musyrif_id
- target_type
- location_id
- active_status

## 6.7 Master Room

Table:
`rooms`

Fields:
- room_id
- building
- room_name
- capacity
- musyrif_id
- active_status

## 6.8 Master Subject

Table:
`subjects`

Fields:
- subject_id
- subject_name
- category
- curriculum_type
- active_status

## 6.9 Master Activity

Table:
`activities`

Fields:
- activity_id
- activity_name
- domain_id
- frequency
- owner_department
- active_status

## 6.10 Master Location

Table:
`locations`

Fields:
- location_id
- location_name
- location_type
- address
- active_status

---

# 7. ID STANDARD

Gunakan ID machine-readable.

Contoh:

- Student: `STU-2026-0001`
- Guardian: `GRD-000001`
- Staff: `STF-0001`
- Halaqah: `HLQ-2026-01`
- Class: `CLS-2026-A1`
- Room: `ROM-A-01`
- Activity: `ACT-TAHFIZH-001`
- Event: `EVT-20260823-000001`
- Report: `RPT-2026-08-STU-0001`
- Case: `CAS-2026-000123`

ID tidak boleh bergantung pada nama manusia.

---

# 8. TRANSACTION ENGINE

Sistem berbasis event/transaksi.

## 8.1 Common Event Fields

Setiap transaksi minimal memiliki:

- event_id
- student_id
- event_type
- domain
- event_datetime
- source_unit
- created_by
- created_at
- submitted_at
- validated_by
- validated_at
- status
- notes
- version
- correction_reference

## 8.2 Tahfizh Transaction

Table:
`tahfizh_transactions`

Fields:
- event_id
- student_id
- halaqah_id
- musyrif_id
- transaction_type
- surah_id
- ayah_from
- ayah_to
- page_from
- page_to
- juz
- result
- quality_score jika digunakan
- error_count jika digunakan
- correction_required
- next_target
- notes
- validation_status

Transaction type:
- tahsin
- ziyadah
- murajaah
- juziyyah
- tasmi
- khatam
- uat
- sanad

## 8.3 Academic Assessment

Table:
`academic_assessments`

Fields:
- assessment_id
- student_id
- subject_id
- teacher_id
- assessment_type
- date
- score
- maximum_score
- normalized_score
- competency
- notes
- status

## 8.4 Attendance

Table:
`attendance_events`

Fields:
- attendance_id
- student_id
- activity_id
- schedule_id
- date
- attendance_status
- reason_code
- recorded_by
- validated_by

Status:
- present
- excused
- sick
- permission
- absent
- late

## 8.5 Discipline Event

Table:
`discipline_events`

Fields:
- case_id
- student_id
- event_date
- category
- severity
- description
- evidence_reference
- recorded_by
- reviewer
- action
- follow_up_date
- status
- resolved_at

Jangan menghapus histori kasus yang valid.
Gunakan status dan resolution.

## 8.6 Coaching / Musyrif Note

Table:
`coaching_sessions`

Fields:
- session_id
- student_id
- musyrif_id
- session_date
- topic_category
- observation
- agreed_action
- target_date
- follow_up_status
- visibility_level
- approved_for_parent_report
- confidential_flag

## 8.7 Health Event

Table:
`health_events`

Hanya jika diperlukan dan dengan kontrol akses tinggi.

Fields minimum:
- health_event_id
- student_id
- date
- category
- status
- action
- referral
- restricted_notes

## 8.8 Permission Event

Table:
`permission_events`

Fields:
- permission_id
- student_id
- permission_type
- start_datetime
- end_datetime
- destination
- guardian_reference
- approval_status
- approved_by
- return_status

---

# 9. DATA LIFECYCLE

Default lifecycle:

`DRAFT → SUBMITTED → VALIDATED → LOCKED → PUBLISHED`

## 9.1 DRAFT
Belum resmi.

## 9.2 SUBMITTED
Penginput menyatakan selesai.

## 9.3 VALIDATED
Validator menyatakan data benar.

## 9.4 LOCKED
Data periode tertentu tidak dapat diedit tanpa correction workflow.

## 9.5 PUBLISHED
Data sudah masuk laporan resmi.

## 9.6 Correction Workflow

`CORRECTION_REQUEST → REVIEW → APPROVED/REJECTED → NEW_VERSION`

Tidak boleh silent edit.

---

# 10. DATA OWNERSHIP MATRIX

Contoh baseline:

| Data | Input | Validator | Owner |
|---|---|---|---|
| Tahfizh harian | Musyrif Halaqah | PIC/Mas'ul Tahfizh | Waka Tahfizh |
| Juziyyah | Penguji | Mas'ul Tahfizh | Waka Tahfizh |
| Akademik | Guru | Admin/PIC Akademik | Waka Akademik |
| Kehadiran akademik | Guru/PIC | Akademik | Waka Akademik |
| Kesantrian | Musyrif/PIC | Koordinator | Waka Kesantrian |
| Ibadah program | Musyrif/PIC | Kesantrian | Waka Kesantrian |
| Coaching | Musyrif | Koordinator | Kesantrian |
| Master Santri | Admin | Sekretariat | Kepala Unit |
| Master Wali | Admin | Sekretariat | Kepala Unit |
| Report Wali | Engine | Reviewer | Kepala/PIC |
| Executive Report | Engine | Kepala Unit | Idaroh/Yayasan |

Matrix final harus disahkan oleh pimpinan.

---

# 11. STUDENT 360 PROFILE

Student 360 adalah tampilan histori terpadu.

Struktur:

`Student Identity`
→ Tahfizh
→ Akademik
→ Ruhiyah Terobservasi
→ Kesantrian
→ Kepengasuhan
→ Bahasa
→ Kegiatan
→ Administrasi
→ Health jika authorized
→ Historical Trend
→ Interventions
→ Parent Reports

Tujuan:
- satu profil;
- satu histori;
- tidak membuka banyak file;
- melihat perkembangan antarperiode.

---

# 12. KPI ENGINE

## 12.1 Prinsip KPI

KPI harus:
- jelas definisi;
- jelas rumus;
- jelas sumber;
- jelas owner;
- jelas frekuensi;
- jelas threshold;
- jelas tindakan.

## 12.2 Tahfizh KPI

Contoh:
- target_juz
- realized_juz
- target_achievement_pct
- ziyadah_rate
- murajaah_consistency
- juziyyah_pass_rate
- tasmi_completion
- halaqah_attendance
- days_without_submission
- monthly_growth

## 12.3 Academic KPI

Contoh:
- attendance_pct
- average_score
- assignment_completion
- subject_mastery
- remedial_count
- academic_trend

## 12.4 Kesantrian KPI

Contoh:
- activity_attendance
- unresolved_case_count
- follow_up_completion
- punctuality_rate
- repeated_issue_count

## 12.5 Kepengasuhan KPI

Contoh:
- coaching_frequency
- open_follow_up_count
- overdue_follow_up
- positive_progress_count

Jangan menjadikan confidential counseling note sebagai KPI umum.

## 12.6 Data Quality KPI

Contoh:
- completeness_pct
- validation_delay
- duplicate_count
- missing_master_reference
- late_submission_count
- correction_rate

---

# 13. EARLY WARNING ENGINE

Early Warning harus berbasis rule transparan.

Contoh:

- Tahfizh tertinggal dari target lebih dari X hari.
- Tidak ada transaksi halaqah selama X hari aktif.
- Kehadiran akademik turun di bawah threshold.
- Pelanggaran kategori tertentu berulang.
- Follow-up pembinaan melewati due date.
- Santri tidak memiliki assignment halaqah aktif.
- Santri tidak memiliki wali aktif.
- Wali belum menerima laporan periode.
- Bidang belum validasi periode.
- Data duplicate.
- Nilai abnormal.
- Record berubah setelah publication.

Setiap alert harus memiliki:
- alert_id;
- category;
- severity;
- created_at;
- owner;
- due_date;
- status;
- resolution.

Status:
`OPEN → ACKNOWLEDGED → IN_PROGRESS → RESOLVED → CLOSED`

---

# 14. REPORTING ARCHITECTURE

Satu database menghasilkan beberapa perspektif.

## 14.1 Musyrif Operational View

Fokus:
- santri binaan;
- tugas hari ini;
- alert;
- follow-up;
- tahfizh;
- attendance;
- coaching.

Pertanyaan utama:
> Siapa yang membutuhkan perhatian hari ini?

## 14.2 Waka Functional View

Fokus:
- performa bidang;
- halaqah/kelas;
- compliance input;
- target vs realisasi;
- unresolved cases;
- trend.

Pertanyaan:
> Unit mana yang membutuhkan intervensi?

## 14.3 Kepala Unit Management View

Fokus:
- institutional health;
- cross-domain KPI;
- risiko;
- capaian target;
- data quality;
- critical cases.

## 14.4 Idaroh / Pusat View

Fokus:
- executive summary;
- performance;
- exception;
- trend;
- target;
- risiko strategis.

## 14.5 Yayasan View

Fokus:
- high-level KPI;
- strategic target;
- institutional performance;
- governance;
- risk.

## 14.6 Parent / Guardian View

Fokus:
- perkembangan anak;
- capaian;
- area perhatian;
- catatan konstruktif;
- target berikutnya.

Tidak boleh menampilkan informasi santri lain.

---

# 15. FORMAT LAPORAN WALI SANTRI

Template baseline:

1. Identitas santri.
2. Periode laporan.
3. Ringkasan perkembangan.
4. Tahfizh.
5. Akademik.
6. Kedisiplinan dan kepengasuhan.
7. Program ruhiyah.
8. Bahasa/kegiatan.
9. Capaian positif.
10. Area yang membutuhkan perhatian.
11. Catatan musyrif.
12. Target periode berikutnya.
13. Status review.
14. Tanggal publication.

Tone:
- objektif;
- tarbawi;
- konstruktif;
- tidak mempermalukan;
- tidak membandingkan;
- tidak membuat diagnosis tanpa dasar.

---

# 16. EXCEL ENGINE — MVP ARCHITECTURE

Excel digunakan sebagai prototype, bukan sebagai arsitektur akhir.

## 16.1 Struktur Workbook

Rekomendasi sheet logical:

- README
- CONFIG
- M_STUDENT
- M_GUARDIAN
- M_STAFF
- M_CLASS
- M_HALAQAH
- M_ROOM
- M_SUBJECT
- M_ACTIVITY
- T_TAHFIZH
- T_ACADEMIC
- T_ATTENDANCE
- T_DISCIPLINE
- T_COACHING
- T_PERMISSION
- KPI
- DASHBOARD
- REPORT_PARENT
- DATA_QUALITY

## 16.2 Standard

Gunakan:
- Excel Tables;
- Power Query;
- relational Data Model;
- Power Pivot;
- DAX jika diperlukan;
- named measures;
- data validation;
- protected configuration;
- no merged cells pada database.

## 16.3 Larangan

Hindari:
- sheet per bulan;
- nama santri diketik ulang;
- formula manual per santri;
- copy-paste rekap;
- banyak file master;
- formula berbeda untuk KPI yang sama;
- warna sebagai satu-satunya indikator data;
- hidden manual correction tanpa log.

## 16.4 Time Modeling

Gunakan satu tabel transaksi dengan kolom tanggal.

Bukan:
- JAN
- FEB
- MAR

Gunakan:
- transaction_date
- month
- quarter
- semester
- academic_year

---

# 17. BI / SEMANTIC MODEL

Jika memakai Power BI atau teknologi sejenis, gunakan semantic layer.

Contoh measures:

- `[Attendance %]`
- `[Tahfizh Achievement %]`
- `[Juziyyah Pass Rate]`
- `[Academic Average]`
- `[Open Follow Up]`
- `[Late Submission Count]`

Satu definisi measure berlaku di seluruh laporan.

Jangan membuat formula KPI berbeda di dashboard berbeda.

---

# 18. WEB APPLICATION BASELINE

## 18.1 Arsitektur Referensi

Backend:
- Laravel atau framework setara.

Database:
- PostgreSQL.

Frontend:
- responsive web application.

API:
- REST/structured API.

Authentication:
- secure user authentication.

Authorization:
- RBAC + scope data.

Storage:
- object/document storage.

Async jobs:
- queue.

Audit:
- activity log.

Backup:
- scheduled + offsite.

## 18.2 Modul Utama

1. Authentication.
2. User & Roles.
3. Student Master.
4. Guardian Master.
5. Staff Master.
6. Academic.
7. Tahfizh.
8. Attendance.
9. Kesantrian.
10. Kepengasuhan.
11. Permission.
12. Health restricted.
13. Activities.
14. Reporting.
15. Dashboard.
16. Alert.
17. Data Quality.
18. Audit.
19. Notification.
20. Export.

---

# 19. ROLE-BASED ACCESS CONTROL

Baseline roles:

- Super Admin.
- Kepala Unit.
- Waka Akademik.
- Waka Tahfizh.
- Waka Kesantrian.
- Sekretariat.
- Admin.
- Guru.
- Musyrif Halaqah.
- Musyrif Kamar.
- Validator.
- Idaroh Viewer.
- Yayasan Viewer.
- Wali Santri.
- Auditor jika diperlukan.

Scope:
- self;
- assigned students;
- assigned class;
- assigned halaqah;
- department;
- unit;
- institution.

---

# 20. SECURITY & PRIVACY ENGINE

Minimum control:

1. TLS/HTTPS.
2. Strong authentication.
3. Password hashing.
4. Least privilege.
5. RBAC.
6. Session control.
7. Audit log.
8. Backup.
9. Recovery test.
10. Data export restriction.
11. Sensitive note restriction.
12. Access review berkala.
13. Account deactivation.
14. Device/session monitoring jika tersedia.
15. Secret management.
16. Patch management.
17. Database backup encryption.
18. Logging.
19. Incident response procedure.
20. Privacy by design.

## 20.1 Sensitive Data

Data seperti:
- kesehatan;
- counseling;
- catatan keluarga;
- catatan pembinaan sensitif;

harus diberi classification.

Contoh:
- PUBLIC_INTERNAL
- INTERNAL
- RESTRICTED
- HIGHLY_RESTRICTED

---

# 21. AI ENGINE

AI hanya menggunakan data yang sudah diizinkan.

Arsitektur:

`VALIDATED DATABASE`
→ query
→ business rules
→ structured context
→ LLM
→ structured output
→ validation
→ human review
→ publication

## 21.1 Use Cases

AI boleh:
- membuat draft laporan wali;
- membuat executive summary;
- meringkas trend;
- mencari data tidak lengkap;
- klasifikasi catatan;
- memberikan query natural language;
- membantu analisis;
- menghasilkan daftar follow-up;
- membantu menyusun laporan bulanan.

## 21.2 Human-in-the-Loop

Wajib human review untuk:
- laporan wali;
- disciplinary summary;
- sensitive coaching;
- executive statements;
- rekomendasi intervensi besar.

## 21.3 Larangan AI

AI tidak boleh:
- mengarang data;
- membuat nilai;
- membuat diagnosis medis;
- menentukan iman/keikhlasan;
- menghukum otomatis;
- menilai karakter absolut;
- mengirim laporan sensitif tanpa approval.

---

# 22. PROMPT ENGINE STANDARD

Prompt production bukan prompt bebas.

Gunakan komponen:

1. SYSTEM POLICY
2. USER ROLE
3. PURPOSE
4. AUTHORIZED DATA
5. BUSINESS RULES
6. OUTPUT SCHEMA
7. STYLE
8. SAFETY RULES
9. EXAMPLES
10. VALIDATION CRITERIA

## 22.1 Structured Input Example

```json
{
  "student": {},
  "period": {},
  "tahfizh": {},
  "academic": {},
  "boarding": {},
  "positive_notes": [],
  "attention_items": [],
  "approved_notes": []
}
```

## 22.2 Report Prompt Rule

AI harus:
- memakai data yang diberikan;
- tidak mengisi gap dengan asumsi;
- menandai missing data;
- tidak membandingkan dengan santri lain;
- menggunakan bahasa konstruktif;
- mempertahankan fakta numerik;
- meminta human review sebelum final.

---

# 23. DATA QUALITY ENGINE

Gunakan rule minimal:

## Completeness
Field wajib terisi.

## Validity
Nilai sesuai tipe/range.

## Referential Integrity
Student_ID harus ada di master.

## Uniqueness
Tidak ada duplicate event.

## Timeliness
Data masuk tepat waktu.

## Consistency
Nilai tidak kontradiktif.

## Freshness
Data terbaru sesuai jadwal.

## Accuracy
Ada mekanisme verifikasi.

Contoh alert:
- missing student_id;
- duplicate tahfizh event;
- invalid score >100;
- halaqah inactive digunakan;
- student inactive mendapat attendance;
- future date tidak valid;
- missing validator.

---

# 24. INTEGRATION ENGINE

Sistem dirancang agar dapat terhubung dengan:

- website;
- Google Workspace jika diperlukan;
- WhatsApp gateway jika legal dan aman;
- email;
- Power BI;
- export Excel;
- PDF report;
- external government system jika tersedia API/resmi;
- future mobile app.

Prinsip:
- API-first jika memungkinkan;
- jangan hard-code integrasi;
- simpan external_id;
- semua integrasi harus logged.

---

# 25. NOTIFICATION ENGINE

Jenis notifikasi:
- reminder input;
- validation pending;
- alert;
- overdue follow-up;
- report published;
- parent notification;
- system incident.

Channel:
- in-app;
- email;
- WhatsApp resmi jika tersedia;
- push notification pada fase lanjut.

Notifikasi harus:
- relevan;
- tidak berlebihan;
- role-based;
- logged.

---

# 26. REPORTING FREQUENCY

Baseline:

## Harian
- operational input;
- attendance;
- halaqah;
- alert;
- follow-up.

## Pekanan
- musyrif review;
- halaqah status;
- unresolved issues;
- data compliance.

## Bulanan
- student progress;
- functional report;
- parent report jika ditetapkan;
- management dashboard.

## Semester
- academic;
- tahfizh;
- holistic student development;
- executive evaluation.

## Tahunan
- institutional performance;
- program effectiveness;
- trend cohort;
- strategic planning.

---

# 27. DECISION GATE UNTUK FITUR BARU

Setiap fitur baru wajib menjawab:

1. Masalah apa yang diselesaikan?
2. Siapa user-nya?
3. Siapa process owner?
4. Apa unit transaksi?
5. Data apa yang dibutuhkan?
6. Data berasal dari mana?
7. Siapa input?
8. Siapa validator?
9. Siapa melihat?
10. Apa output?
11. Keputusan apa yang dibantu?
12. Apakah data sensitif?
13. Perlukah audit trail?
14. Perlukah integration?
15. Apakah perlu AI?
16. Bagaimana success metric?
17. Apa failure mode?
18. Apa fallback manual?

Jika belum jelas, fitur belum boleh masuk production.

---

# 28. MATURITY ROADMAP

## Level 0 — Fragmented Data
- WA;
- kertas;
- Excel terpisah;
- rekap manual.

## Level 1 — Standardized Data
- ID;
- format baku;
- master data;
- ownership.

## Level 2 — Excel Engine
- Power Query;
- Data Model;
- dashboard;
- automated reports.

## Level 3 — Central Web System
- PostgreSQL;
- centralized operations;
- RBAC;
- audit.

## Level 4 — Business Intelligence
- semantic model;
- cross-domain dashboard;
- KPI.

## Level 5 — Early Warning
- automated alerts;
- exception monitoring.

## Level 6 — AI Augmentation
- reporting;
- analysis;
- natural language;
- assisted workflow.

## Level 7 — Institutional Intelligence
- longitudinal analysis;
- program evaluation;
- multi-unit reporting;
- decision support.

Prinsip:
> Jangan melompat ke AI sebelum data dan workflow stabil.

---

# 29. PRIORITAS IMPLEMENTASI

## Fase A — Governance Foundation
Output:
- organizational data map;
- process map;
- role map;
- ownership matrix.

## Fase B — Data Foundation
Output:
- Master Data Dictionary;
- ID Standard;
- field definition;
- data contract;
- validation rule.

## Fase C — Excel MVP
Output:
- master tables;
- transaction tables;
- Power Query;
- Data Model;
- dashboard;
- parent report prototype.

## Fase D — Pilot
Pilih satu domain:
- Tahfizh;
atau
- satu kelompok santri.

Evaluasi:
- input friction;
- validation;
- missing fields;
- KPI usefulness;
- report usefulness.

## Fase E — Web MVP
Output:
- authentication;
- master data;
- core transactions;
- reporting;
- audit.

## Fase F — BI
Output:
- semantic model;
- management dashboard;
- executive reporting.

## Fase G — AI
Output:
- AI report draft;
- AI query;
- anomaly summary;
- assisted follow-up.

---

# 30. DATABASE DESIGN PRINCIPLES

1. Primary key unik.
2. Foreign key jelas.
3. Jangan simpan nama sebagai relasi.
4. Jangan duplicate master.
5. Gunakan timestamps.
6. Gunakan status.
7. Hindari free text jika dapat memakai controlled vocabulary.
8. Free text tetap tersedia untuk konteks.
9. Gunakan soft delete bila perlu.
10. Data penting harus versioned/audited.
11. Pisahkan transactional vs reference data.
12. Pisahkan sensitive table jika perlu.
13. Normalisasi secukupnya.
14. Reporting boleh memakai materialized/semantic layer.
15. Jangan optimasi prematur.

---

# 31. CONTROLLED VOCABULARY

Kategori penting harus punya daftar resmi.

Contoh:

Attendance:
- PRESENT
- SICK
- EXCUSED
- PERMISSION
- ABSENT
- LATE

Tahfizh:
- TAHSIN
- ZIYADAH
- MURAJAAH
- JUZIYYAH
- TASMI
- UAT
- SANAD

Case status:
- OPEN
- IN_REVIEW
- FOLLOW_UP
- RESOLVED
- CLOSED

Validation:
- DRAFT
- SUBMITTED
- VALIDATED
- LOCKED
- PUBLISHED

---

# 32. PARENT REPORTING ETHICS

Laporan wali harus:
- benar;
- relevan;
- tidak mempermalukan;
- tidak membandingkan;
- tidak terlalu teknis;
- membedakan fakta dan catatan;
- menyebut hal positif;
- menyebut area perhatian dengan bahasa membina;
- memberikan arah tindak lanjut.

Tidak semua catatan internal harus dikirim kepada wali.

Gunakan field:
`approved_for_parent_report`

---

# 33. MANAGEMENT REPORTING ETHICS

Laporan manajemen harus:
- berbasis data;
- menunjukkan sumber;
- membedakan fakta dan interpretasi;
- menunjukkan missing data;
- tidak menyembunyikan kualitas data buruk;
- memperlihatkan trend;
- memberikan exception;
- menghindari vanity metrics.

---

# 34. REPORT VERSIONING

Setiap laporan resmi memiliki:
- report_id;
- period;
- student_id atau organizational_scope;
- generated_at;
- reviewed_by;
- approved_by;
- version;
- published_at;
- superseded_by jika direvisi.

---

# 35. BACKUP & BUSINESS CONTINUITY

Minimum:
- backup harian;
- backup mingguan;
- offsite copy;
- restore test;
- dokumentasi recovery;
- owner recovery;
- recovery priority.

Critical data:
1. master student;
2. transactions;
3. audit;
4. report;
5. config.

---

# 36. CHANGE MANAGEMENT

Setiap perubahan sistem mencatat:
- change_id;
- description;
- reason;
- requested_by;
- approved_by;
- impact;
- migration plan;
- rollback;
- date;
- version.

---

# 37. VERSIONING DOKUMEN ENGINE

Gunakan semantic version:

- 1.0 = baseline resmi
- 1.1 = penambahan minor
- 1.2 = revisi minor
- 2.0 = perubahan arsitektur besar

Changelog wajib disimpan.

---

# 38. SOURCE PRIORITY RULE

Jika terjadi konflik informasi:

1. Keputusan resmi Yayasan/Idaroh.
2. Keputusan resmi pimpinan IMTAQ.
3. SOP yang disahkan.
4. Base Engine versi terbaru.
5. Data dictionary versi terbaru.
6. Implementasi aplikasi.
7. Spreadsheet operasional.
8. Catatan informal.

Software tidak boleh dianggap lebih benar daripada keputusan kelembagaan yang disahkan.

---

# 39. SYSTEM DESIGN REVIEW BOARD

Untuk setiap modul besar, lakukan review dari perspektif:

### Education
Apakah mendukung tarbiyah?

### Data
Apakah struktur data benar?

### Governance
Siapa owner dan validator?

### UX
Apakah pengguna mampu menjalankannya?

### Reporting
Apakah output actionable?

### Security
Apakah akses tepat?

### AI
Apakah AI benar-benar perlu?

### Maintenance
Apakah mudah dikelola?

---

# 40. DEFINITION OF DONE

Sebuah modul belum dianggap selesai hanya karena “bisa dipakai”.

Minimum Definition of Done:

- business process jelas;
- data dictionary ada;
- ownership jelas;
- validation jelas;
- role access benar;
- audit tersedia;
- error handling ada;
- reporting teruji;
- data quality rule tersedia;
- user testing dilakukan;
- SOP tersedia;
- backup/recovery dipertimbangkan;
- privacy review dilakukan.

---

# 41. ANTI-PATTERN YANG HARUS DIHINDARI

1. Membuat dashboard sebelum data model.
2. Membuat AI sebelum data quality.
3. Membuat aplikasi sebelum process mapping.
4. Satu programmer menentukan seluruh business rule.
5. Setiap bidang membuat master santri sendiri.
6. Nama santri sebagai primary key.
7. Sheet per bulan sebagai database.
8. Copy-paste sebagai pipeline utama.
9. Mengubah data lama tanpa audit.
10. Semua user sebagai admin.
11. Menampilkan semua data kepada semua pejabat.
12. AI mengirim laporan tanpa review.
13. KPI tanpa definisi resmi.
14. Mengukur hal yang tidak dapat diobservasi.
15. Laporan hanya berisi angka tanpa tindak lanjut.
16. Sistem rumit yang tidak dipakai musyrif.
17. Menggunakan teknologi baru hanya karena tren.

---

# 42. TARGET AKHIR SISTEM

Target jangka panjang:

## Untuk Musyrif
Satu layar menunjukkan:
- santri binaan;
- alert;
- follow-up;
- perkembangan;
- input cepat.

## Untuk Kepala Bidang
Satu dashboard menunjukkan:
- performa;
- target;
- compliance;
- risiko.

## Untuk Kepala Unit
Satu dashboard menunjukkan:
- kesehatan program;
- cross-domain issue;
- strategic attention.

## Untuk Idaroh
Satu executive view:
- KPI;
- trend;
- risiko;
- exception.

## Untuk Yayasan
Satu institutional view:
- performance;
- target;
- governance;
- strategy.

## Untuk Wali
Satu laporan:
- perkembangan putra;
- capaian;
- perhatian;
- target;
- catatan pembinaan yang telah disetujui.

---

# 43. CANONICAL SYSTEM FLOW

```text
REAL-WORLD ACTIVITY
        ↓
DATA CAPTURE
        ↓
VALIDATION
        ↓
TRANSACTION STORE
        ↓
DATA QUALITY
        ↓
SEMANTIC/KPI LAYER
        ↓
ANALYTICS
        ↓
EARLY WARNING
        ↓
REPORTING
        ↓
AI ASSISTANCE
        ↓
HUMAN REVIEW
        ↓
DISTRIBUTION
        ↓
FOLLOW-UP
        ↓
NEW REAL-WORLD ACTIVITY
```

Sistem harus membentuk **closed feedback loop**.

---

# 44. BASE ENGINE PROMPT UNTUK PROJECT

Jika file ini digunakan sebagai source project, gunakan instruksi kerja berikut:

> Jadikan IMTAQ CORE ENGINE sebagai baseline utama dalam seluruh analisis, desain, dan pengembangan SISTEM IMTAQ. Jangan langsung membuat dashboard, Excel, database, web application, atau fitur AI tanpa memeriksa keterkaitannya dengan business process, master data, transaction model, data ownership, validation, reporting hierarchy, security, dan tujuan pendidikan IMTAQ.
>
> Untuk setiap permintaan:
> 1. identifikasi domain;
> 2. identifikasi process owner;
> 3. tentukan data input;
> 4. tentukan source of truth;
> 5. tentukan transaction grain;
> 6. tentukan validator;
> 7. tentukan role access;
> 8. tentukan KPI jika relevan;
> 9. tentukan report consumer;
> 10. tentukan kebutuhan audit;
> 11. evaluasi security/privacy;
> 12. baru tentukan Excel, web, BI, automation, atau AI sebagai solusi.
>
> Jika kebutuhan pengguna bertentangan dengan prinsip Base Engine, jelaskan konflik dan usulkan desain yang lebih konsisten.
>
> Selalu prioritaskan sistem yang sederhana, dapat dijalankan musyrif/asatidz, terukur, aman, dan dapat berkembang.
>
> Jangan menganggap AI sebagai sumber kebenaran. Semua output AI yang berpengaruh kepada penilaian santri, wali, disiplin, atau keputusan lembaga harus berbasis data tervalidasi dan mengikuti human review.
>
> Pertahankan orientasi utama: membantu tarbiyah dan perkembangan santri, bukan sekadar digitalisasi administrasi.

---

# 45. PRIORITAS TUGAS BERIKUTNYA

Setelah Base Engine ini disahkan, urutan dokumen lanjutan yang direkomendasikan:

## Dokumen 1
**IMTAQ Master Data Dictionary v1.0**

## Dokumen 2
**IMTAQ Business Process Map v1.0**

## Dokumen 3
**IMTAQ Data Ownership & RACI Matrix v1.0**

## Dokumen 4
**IMTAQ Student Reporting Framework v1.0**

## Dokumen 5
**IMTAQ Excel Engine Specification v1.0**

## Dokumen 6
**IMTAQ Database Schema v1.0**

## Dokumen 7
**IMTAQ Web Application Specification v1.0**

## Dokumen 8
**IMTAQ AI & Prompt Governance v1.0**

## Dokumen 9
**IMTAQ Security & Privacy Standard v1.0**

## Dokumen 10
**IMTAQ Implementation Roadmap v1.0**

---

# 46. STATUS

**Status dokumen:** BASELINE  
**Versi:** 1.0  
**Tanggal:** 23 Agustus 2026  
**Fungsi:** Source utama project SISTEM IMTAQ  
**Next recommended artifact:** IMTAQ Master Data Dictionary v1.0

---

# 47. RINGKASAN EKSEKUTIF

IMTAQ CORE ENGINE dibangun di atas lima lapisan utama:

1. **Education Engine**
   - menjaga sistem tetap berorientasi pada tarbiyah dan perkembangan santri.

2. **Data Engine**
   - memastikan data memiliki ID, struktur, ownership, validation, history, dan quality.

3. **Reporting & BI Engine**
   - mengubah transaksi menjadi KPI, dashboard, report, dan early warning.

4. **Application Engine**
   - menyediakan web application yang aman, role-based, dan scalable.

5. **AI Engine**
   - menambahkan kemampuan analisis dan narasi setelah data dan proses stabil.

Keseluruhan sistem berorientasi pada satu prinsip:

> **Sistem IMTAQ harus membantu manusia mendidik santri dengan lebih tepat, bukan menggantikan proses tarbiyah dengan teknologi.**
