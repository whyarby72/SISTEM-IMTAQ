# 14 — MVP / Future / Superseded Register

## MVP — build when sprint plan authorizes
- canonical student/staff/class/subject/semester references
- student identifiers/status/enrollment history
- homeroom assignments
- teaching assignments
- academic calendar
- flexible schedule rules
- session generation
- schedule changes
- student session participants
- student attendance + open correction + period lock architecture
- teacher participation structure
- semester final grade structure
- report card versioning
- academic history and transcript versioning
- shared audit/correction
- KPI semantic definitions
- DQ/shared alert infrastructure
- migration infrastructure
- RBAC/least privilege

## FUTURE — do not build as MVP dependency
- detailed assessment engine: Tugas/Quiz/UTS/UAS/practical/oral etc.
- configurable grading schemes/weights.
- remedial attempts/engine.
- competency-based assessment engine.
- GPA/IP/IPK.
- rankings.
- full cross-domain IMTAQ Parent Development Report.
- Student 360 cross-domain UI beyond Academic needs.
- AI-native shared assistant: read queries/summaries, structured drafts and later controlled actions under `docs/08_ai/`.
- predictive models.
- student profile photo/file storage.
- digital signature graphics.
- complex data warehouse/streaming architecture.
- Google/Gmail SSO and Google Workspace login integration; exact production login provider is deferred and is not a current implementation dependency.

## SUPERSEDED — DO NOT IMPLEMENT
- paper attendance as primary Source of Truth.
- mandatory paper-to-digital transcription batch workflow.
- mandatory `attendance_entry_batches`.
- Guru as normal online student-attendance inputter.
- routine Academic Admin validation of every student attendance row.
- `classes.homeroom_staff_id` as canonical homeroom source.
- `students.class_id_current` as canonical class source.
- `students.status` as sole lifecycle source.
- NIS/NISN as student identity/primary key.
- missing attendance auto-converted to ABSENT.
- cancelled session mass absence.
- fixed institution-wide academic time slots or fixed daily session count.
- Tugas/Quiz/UTS/UAS fields in the MVP grade table.
- grading weights/remedial as MVP.
- automatic KKM/mastery classification without policy.
- generic Attendance % with developer-invented formula.
- developer-invented Early Warning thresholds.
- composite Academic Score.
- composite Student Risk Score.
- Report Card as a grade-entry surface.
- Report PDF as Transcript source.

### AI future guardrails
Future status does **not** mean ungoverned experimentation. The active architecture is `docs/08_ai/`: provider secrets server-side, same RBAC/data scope, allowlisted tools, no arbitrary SQL, human confirmation for writes, and AI never becomes Source of Truth.


## Shared Communication / WhatsApp
`FUTURE` / `DESIGN_READY_FUTURE`:
- canonical Guardian + Staff/Organization recipient/audience foundations;
- provider-neutral communication engine;
- `ANNOUNCEMENT`, `REMINDER`, and structured `ACTION_REQUEST`;
- dynamic/audited staff audiences and managed groups;
- future teacher availability/Tomorrow Readiness (`CONFIRMED ≠ PRESENT`, `NO_RESPONSE ≠ ABSENT`);
- WhatsApp adapter with delivery/approved-response webhook normalization;
- personalized parent-report delivery;
- delivery/response observability, retry, exception and audit;
- multi-domain expansion after source modules are production-ready.

Do not place direct WhatsApp provider calls inside Academic/Tahfizh/Kesantrian modules. Do not build these features in the current MVP merely because the architecture is ready.

## Biometric / Fingerprint Presence — FUTURE CAPABILITY ONLY
`FUTURE` / `DEFERRED_NOT_REQUIRED`:
- fingerprint/biometric integration is **not an MVP dependency** and no device/vendor/API implementation is authorized now;
- preserve future compatibility through canonical `Student_ID` / `Staff_ID` and domain-owned attendance workflows;
- a future biometric scan is supporting presence evidence, not automatically official Academic/Tahfizh/Kesantrian attendance;
- no scan must never be converted automatically to `ABSENT`;
- the current attendance workflow must remain fully usable when biometric infrastructure is absent/offline;
- biometric templates/raw biometric data must not be exposed to ordinary domain modules, reporting, Parent Portal or AI;
- detailed vendor selection, device schema, synchronization, retention and biometric privacy controls are intentionally deferred until a real institutional need passes a future Decision Gate.

**Codex instruction:** do not create fingerprint tables, packages, UI, hardware adapters, migrations or tasks in the current implementation merely because this future capability is listed.
