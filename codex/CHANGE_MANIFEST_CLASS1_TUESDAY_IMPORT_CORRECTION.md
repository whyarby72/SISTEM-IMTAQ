# Change Manifest — Class 1 Tuesday Import Correction

- **Change ID:** CORR-IMPORT-CLASS1-TUESDAY
- **Date:** 2026-09-09
- **Scope:** Local development data correction
- **Outcome:** Jadwal Selasa Kelas 1 periode semester pertama disamakan dengan jadwal Senin.

## Correction

- Selasa 07:00–09:30 → 08:00–09:30 — Bahasa Arab — الأستاذ أبو عبد الله.
- Selasa 10:15–11:30 → 10:00–11:00 — Bahasa Arab — الأستاذ ذكي.
- 52 sesi PLANNED diperbarui; tidak ada sesi yang memiliki data kehadiran.

## Safety

- Data kehadiran: NONE DELETED; attendance rows verified zero before correction.
- Audit: 52 `schedule_changes` dengan `change_type=IMPORT_CORRECTION` dan alasan koreksi import.
- Migrations/seed/import/RBAC/business rules: UNCHANGED.
- Scope: local PostgreSQL development data only.

## Verification

- 19 related tests / 100 assertions: PASS.
- Blade cache and PHP lint: PASS.
- Post-correction read-back: Monday and Tuesday rules/time/teacher pairing match; 52 correction records present.

**Status:** DONE
