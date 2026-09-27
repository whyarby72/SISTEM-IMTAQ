# Codex Change Management & Safe Maintenance Architecture v1.0

**Status:** `DESIGN_LOCKED`  
**Scope:** seluruh source code, database migration, configuration, tests, documentation, deployment artifacts dan maintenance SISTEM IMTAQ.  
**Purpose:** memastikan perubahan yang dikerjakan melalui Codex dapat dilacak, diuji, dibatasi dampaknya, di-deploy dengan aman, dan di-rollback tanpa mengharuskan user mengetahui file mana yang harus diedit atau di-upload secara manual.

## 1. Prinsip utama

User menyampaikan **masalah, tujuan, role, modul, dan perilaku yang diharapkan**. User tidak diwajibkan mengetahui controller, model, migration, route, file frontend, atau file deployment yang harus diubah.

Codex bertanggung jawab terhadap:

1. impact analysis;
2. penentuan owner module;
3. identifikasi file/contract/database/RBAC yang terdampak;
4. perubahan minimum yang diperlukan;
5. automated tests dan regression scope;
6. change manifest;
7. staging/deployment readiness;
8. rollback path.

Canonical flow:

`REQUEST → IMPACT ANALYSIS → CHANGE PLAN → ISOLATED BRANCH → IMPLEMENT → TEST → CHANGE MANIFEST → STAGING → APPROVAL → PRODUCTION DEPLOY → SMOKE TEST → CLOSE`

Untuk bug:

`BUG REPORT → REPRODUCE → TRACE CURRENT VERSION → IMPACT CHECK → MINIMAL PATCH → REGRESSION TEST → CHANGE MANIFEST → STAGING → DEPLOY/ROLLBACK DECISION`

## 2. Git adalah Source of Truth untuk application source code

Production source code tidak boleh dikelola sebagai kumpulan ZIP/FTP overwrite tanpa version history.

Minimum requirement sebelum business implementation berjalan:

- repository Git tersedia;
- branch utama yang disepakati, default `main`;
- setiap change memiliki commit history;
- commit dapat dihubungkan ke Task ID / Change ID;
- production deploy mengacu ke Git commit/tag yang diketahui;
- rollback aplikasi mengacu ke known-good commit/release.

Contoh:

```text
main
 └── feature/CHG-ACA-021-missing-attendance-warning
       ├── implementation
       ├── tests
       └── change manifest
```

Codex tidak boleh mengandalkan nama seperti `website-final.zip`, `final-baru.zip`, atau overwrite file manual sebagai mekanisme version control.

## 3. Branch isolation

Perubahan non-trivial dilakukan pada branch terisolasi bila environment/tooling mendukung Git branch workflow.

Naming guidance:

- `feature/<change-id>-<short-name>`
- `fix/<change-id>-<short-name>`
- `chore/<task-id>-<short-name>`

Direct change ke production/main tanpa review/testing path tidak boleh menjadi workflow normal.

Emergency hotfix tetap membutuhkan:

- Change ID;
- impact statement;
- minimal patch;
- automated regression sesuai risiko;
- post-deploy smoke test;
- change manifest;
- commit history.

## 4. Change classification wajib

Sebelum coding, Codex harus membaca `modules/CHANGE_IMPACT_RULES.md` dan menetapkan satu atau lebih kelas:

- `MODULE_INTERNAL`
- `MODULE_CONTRACT`
- `SHARED_CORE`
- `CROSS_DOMAIN`
- `DATABASE_GLOBAL`
- `SECURITY_GLOBAL`
- `AI_PLATFORM`
- `COMMUNICATION_PLATFORM`
- `PARENT_PORTAL`

Change classification menentukan regression, review, migration dan rollback depth.

## 5. Protected zones

Beberapa area dianggap **protected architecture zones**. Perubahan module-internal tidak boleh menyentuhnya hanya karena lebih mudah secara coding.

Default protected zones:

- canonical Student identity/lifecycle;
- canonical Staff/Guardian identity and relationship contracts;
- Shared Core contracts;
- Auth/RBAC/authorization scope engine;
- append-only audit infrastructure;
- correction/versioning infrastructure;
- shared database/global storage configuration;
- shared semantic/reporting contracts;
- AI platform boundary;
- Communication provider/orchestrator boundary;
- Parent Portal authorization boundary;
- secrets/environment configuration.

Jika perubahan yang semula `MODULE_INTERNAL` ternyata membutuhkan protected-zone change:

`STOP → RECLASSIFY IMPACT → IDENTIFY CONSUMERS → PLAN COMPATIBILITY → PROCEED ONLY UNDER BROADER CHANGE GATE`

Codex tidak boleh silently broaden scope.

## 6. Minimum Necessary Change

Untuk setiap request, Codex wajib memilih perubahan terkecil yang memenuhi business outcome dan menjaga architecture contracts.

Dilarang sebagai efek samping task kecil tanpa Change Request terpisah:

- refactor besar lintas modul;
- rename/move massal file yang tidak diperlukan;
- framework/library major upgrade;
- auth rewrite;
- schema redesign global;
- perubahan public contract;
- perubahan formula KPI/reporting;
- activation fitur `POLICY_PENDING`/`FUTURE`;
- cleanup yang menghapus historical behavior/evidence.

Jika refactor memang diperlukan untuk correctness/security, Codex harus menjelaskan dependency tersebut di impact plan.

## 7. Pre-change impact statement

Sebelum implementasi perubahan non-trivial, Codex harus merekam:

- Change/Task ID;
- problem/outcome;
- owner module/workstream;
- change class;
- affected modules;
- source-of-truth entity/service;
- contracts touched;
- RBAC/privacy/security impact;
- database/migration impact;
- backward-compatibility impact;
- feature-flag requirement;
- test/regression scope;
- deployment impact;
- rollback plan;
- policy/management decision required or not.

Use `templates/CHANGE_IMPACT_TEMPLATE.md`.

## 8. File-scope discipline

Codex harus menyatakan file scope setelah impact analysis.

Example:

```text
Expected write scope:
- application/web/app/Domains/Academic/**
- application/web/tests/Feature/Academic/**
- docs/03_operations/... if contract changed

Protected/not expected:
- application/web/app/Shared/Core/**
- application/web/app/Shared/Platform/Auth/**
- Domains/Tahfizh/**
```

Jika implementasi membutuhkan file di luar expected write scope, Codex harus memperbarui impact statement sebelum edit tersebut dilakukan.

Tidak ada asumsi bahwa seluruh repository boleh ditulis hanya karena Codex mempunyai filesystem access.

## 9. Database migration safety

### 9.1 Applied migration immutable

Migration yang sudah pernah diterapkan ke shared/staging/production database **tidak boleh diedit untuk mengubah sejarah schema**.

Perubahan berikutnya dibuat sebagai migration baru.

```text
old migration (applied) → immutable
new requirement → new migration
```

### 9.2 Production migration rules

Setiap migration harus dievaluasi untuk:

- lock/downtime risk;
- data-loss risk;
- nullable/backfill strategy;
- default behavior;
- index/constraint creation impact;
- backward compatibility dengan application version sebelumnya/sementara;
- rollback or forward-fix strategy;
- backup requirement.

Destructive operations seperti drop column/table, irreversible data rewrite, mass delete, atau identifier replacement membutuhkan explicit elevated review dan backup/restore evidence.

### 9.3 Expand/contract preference

Untuk perubahan schema berisiko, gunakan pola kompatibel bila memungkinkan:

`EXPAND → DEPLOY COMPATIBLE CODE → BACKFILL/VERIFY → SWITCH READ/WRITE → CONTRACT LATER`

Jangan melakukan destructive contract dalam satu langkah jika safe transition diperlukan.

## 10. Persistent storage separation

Application code yang dapat diganti saat deploy harus dipisahkan dari persistent data.

Persistent data mencakup, bila nanti digunakan:

- uploaded files;
- generated official artifacts/PDF;
- storage-backed attachments;
- database;
- backups;
- provider/webhook durable records.

Deploy aplikasi tidak boleh menghapus atau overwrite persistent user/business data.

## 11. Secrets/environment separation

File seperti `.env` dan production secrets tidak boleh dijadikan bagian dari patch business module atau repository commit.

Examples:

- DB credentials;
- app secret/key;
- WhatsApp/provider tokens;
- future `OPENAI_API_KEY`;
- SMTP/provider credentials.

Change Manifest harus menyatakan apakah ada **environment variable change** tanpa menampilkan secret value.

## 12. Feature flags untuk perubahan yang belum siap aktif

Gunakan feature flag bila deployment kode perlu dipisahkan dari activation, terutama untuk:

- pilot fitur baru;
- `POLICY_PENDING` dependent path yang structurally prepared;
- AI/Communication future feature;
- staged rollout ke role/unit tertentu;
- risky UI/workflow replacement.

Feature flag tidak boleh dipakai untuk mengakali authorization. RBAC backend tetap wajib.

## 13. Staging sebelum production

Production change non-trivial harus mempunyai staging path bila environment production sudah ada.

Recommended environments:

`DEVELOPMENT → STAGING → PRODUCTION`

Staging digunakan untuk:

- migration rehearsal;
- automated tests;
- smoke/UAT scenario;
- role/scope check;
- report rendering check;
- integration/provider check bila relevan;
- deployment command verification.

Production tidak boleh menjadi test environment normal.

## 14. Deployment must be version-aware

Setiap deploy harus dapat ditelusuri minimal ke:

- release/deployment ID;
- Git commit SHA/tag;
- environment;
- deployed_at;
- deployed_by/actor;
- migrations included;
- feature flags changed;
- smoke-test result.

Manual FTP/file-by-file upload bukan canonical deployment method.

Minimum acceptable early-stage deployment adalah scripted/repeatable pull/build/migrate procedure yang mengacu ke known Git revision. Target production kemudian dapat menggunakan CI/CD.

## 15. Rollback strategy

Rollback dibedakan menjadi:

### Application rollback
Return ke known-good application commit/release bila schema masih kompatibel.

### Feature rollback
Disable feature flag jika feature deployment aman tetapi activation bermasalah.

### Database rollback/forward-fix
Database rollback tidak boleh diasumsikan mudah. Jika migration irreversible/destructive, harus ada backup dan forward-fix/restore plan sebelum production.

### Operational rollback
Untuk workflow/configuration change, mungkin berupa restore configuration, deactivate rule, or revert controlled setting; bukan menghapus historical business facts.

Rollback tidak boleh menghapus audit/history yang sah.

## 16. Mandatory Change Manifest

Setiap completed non-trivial code change menghasilkan `Change Manifest` menggunakan `templates/CHANGE_MANIFEST_TEMPLATE.md`.

Minimum fields:

- Change ID / Task ID;
- summary;
- owner module;
- change class;
- files added/modified/deleted;
- database migrations;
- public/shared contracts changed;
- RBAC/security/privacy impact;
- environment/config change;
- feature flags;
- tests run/results;
- regression scope;
- staging result;
- deploy readiness;
- rollback procedure;
- known limitations;
- Git commit/release reference.

User tidak perlu menentukan file mana yang di-upload. Change Manifest menjelaskan apa yang berubah, sedangkan deployment process menentukan deployment unit.

## 17. Change safety gate by impact

### Low-risk module-internal

`REQUEST → IMPACT CHECK → BRANCH → MINIMAL CHANGE → MODULE TEST → SMOKE → MANIFEST → STAGING/DEPLOY`

### Contract/shared/security/global DB

`REQUEST → IMPACT ANALYSIS → CONSUMER MAP → POLICY/APPROVAL IF NEEDED → COMPATIBILITY/MIGRATION PLAN → BRANCH → IMPLEMENT → CROSS-MODULE/SECURITY TEST → MANIFEST → STAGING → APPROVAL → DEPLOY`

### High-risk destructive database/security/auth

`REQUEST → ELEVATED REVIEW → BACKUP/RESTORE/COMPATIBILITY PLAN → REHEARSAL → IMPLEMENT → FULL REQUIRED REGRESSION → STAGING → GO/NO-GO → DEPLOY → OBSERVE`

## 18. Bug maintenance workflow

User cukup memberikan fakta operasional, misalnya:

```text
BUG:
Wali Kelas 1B tidak bisa finalize attendance.

EXPECTED:
Wali kelas aktif harus bisa finalize kelasnya sendiri.

ACTUAL:
Access denied.

SEJAK:
Setelah perubahan terakhir / tanggal diketahui bila ada.
```

Codex kemudian wajib:

1. identify current Git revision;
2. reproduce or create failing test;
3. trace permission/workflow through canonical contracts;
4. classify impact;
5. implement minimal patch;
6. run regression;
7. issue Change Manifest;
8. recommend staging/deploy/rollback decision.

User tidak perlu mengetahui nama file.

## 19. Change log vs business audit

Git history/Change Manifest adalah **software-change audit**.

`audit_logs`, report versions, correction history dan transaction audit adalah **business-data audit**.

Keduanya berbeda dan tidak boleh saling menggantikan.

## 20. Codex stop/escalation conditions

Codex harus berhenti dan eskalasi bila:

- requested patch memerlukan change di protected zone yang belum di-declare;
- migration berpotensi destructive tanpa backup/compatibility plan;
- request mengubah cross-module public contract tanpa consumer analysis;
- request mengubah RBAC/security boundary tanpa negative regression plan;
- policy institutional belum ada;
- production fix meminta silent direct DB edit;
- source repository/current deployed version tidak dapat diidentifikasi;
- tests menunjukkan regression P0/P1;
- rollback/recovery path tidak memadai untuk high-risk deploy.

## 21. Prohibited maintenance patterns

Do not use as normal practice:

- blind full-site overwrite;
- manual selection of "which PHP files to upload" by the business user;
- edit production source code directly through file manager;
- edit an old applied migration;
- direct production DB correction without business correction/audit workflow;
- untracked `hotfix-final.php` style files;
- mass refactor mixed into a small bug fix;
- change framework/dependencies without task scope;
- copy production secrets into Codex prompts/repository;
- bypass tests because a patch is "small" when it affects integrity/security.

## 22. Sprint 0 requirements created by this contract

Before Gate A can be considered complete, Sprint 0 must establish at minimum:

- Git/repository conventions and branch policy;
- repeatable local test commands;
- change ID/commit convention;
- `.gitignore` and secret separation;
- migration immutability rule documented;
- Change Manifest template available;
- basic CI/local verification workflow;
- release/deployment metadata convention;
- documented dev→staging→production target, even if staging infrastructure is provisioned later;
- no business deployment requires the user to identify individual files to upload.

## 23. Architectural decisions

`MAINT-001` Git is the Source of Truth for application source/version history.  
`MAINT-002` User specifies business change; Codex owns file-level impact analysis.  
`MAINT-003` Non-trivial changes require declared Change Class and impact statement.  
`MAINT-004` Protected zones cannot be silently modified by lower-scope tasks.  
`MAINT-005` Minimum Necessary Change is the default patch strategy.  
`MAINT-006` Applied migrations are immutable; schema evolution uses new migrations.  
`MAINT-007` Persistent business storage and secrets are outside replaceable code deploy units.  
`MAINT-008` Non-trivial production changes use development/staging/production promotion.  
`MAINT-009` Every non-trivial completed change produces a Change Manifest.  
`MAINT-010` Production deploy is version-aware and repeatable; manual file-by-file upload is non-canonical.  
`MAINT-011` Rollback strategy is planned before high-risk deploy.  
`MAINT-012` Software change history and business audit history are separate controls.  
`MAINT-013` No direct production data fix may bypass the owning business correction/audit workflow.  
`MAINT-014` Codex must stop/escalate when requested scope expands into protected/shared/security/global risk without an updated plan.


## Session checkpoint companion contract
Implementation/maintenance work is also governed by `CODEX_CHECKPOINT_TIMEBOX_PROTOCOL.md`. Change safety controls what may be changed; the checkpoint protocol controls how far Codex may proceed in one owner-approved local session horizon and when `SAFE_TO_CLOSE` may be reported.
