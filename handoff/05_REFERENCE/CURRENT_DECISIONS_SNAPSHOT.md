# Current Decisions Snapshot — v1.16 Handoff

This is a navigation summary, not a replacement for authoritative documents.

- Product: one SISTEM IMTAQ modular monolith, Laravel/PostgreSQL target.
- One canonical Student_ID and shared Student/Staff/Guardian identity foundation.
- Academic is the first `DESIGN_READY` domain; other baseline domains remain `NOT_PLANNED`.
- Current implementation task: `IMP-S0-001`.
- Academic classes support year-specific Grade Level + configurable Section/Rombel A/B/C/... .
- Schedule conflict engine hard-blocks teacher/class time overlap with transactional recheck/concurrency protection.
- Student attendance online authority: Wali Kelas; missing record never means ABSENT.
- Executive roles Kepala Unit/Idaroh/Yayasan are read-only reporting roles by default.
- AI: future, OpenAI API selected; initial access Super Admin only; core application must work without AI.
- Google/Gmail SSO: deferred, not a current dependency.
- Shared Communication: future; Announcement/Reminder/Action Request/Parent delivery architecture exists.
- Parent Portal: future read-only first.
- Fingerprint/biometric: future capability only, not an MVP dependency.
- Safe maintenance: Git/release history, impact classification, protected zones, Minimum Necessary Change, immutable applied migrations, staging and Change Manifest.


## Codex checkpoint/session control
- After one coherent atomic step, Codex stops at a safe checkpoint.
- Codex reports `SAFE_TO_CLOSE = YES/NO` and exact resume point.
- The owner chooses the next time horizon from 2–4 options with estimated ranges and confidence.
- Time estimates are ranges, not guarantees; no fake 5-minute option if technically unsafe.
