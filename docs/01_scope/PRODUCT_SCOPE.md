# 01 — Product Scope

## Product
The application shell is **SISTEM IMTAQ**, not a standalone attendance app.

Architecture: **modular monolith first**.
- One Laravel-equivalent backend.
- One PostgreSQL database.
- One authentication/RBAC system.
- One audit infrastructure.
- One backup/recovery strategy.

## Academic MVP scope
1. Student/Class academic master references.
2. Teaching assignments.
3. Flexible class-specific scheduling.
4. Academic calendar exceptions.
5. Class session generation.
6. Student attendance.
7. Teacher obligation/participation structure.
8. Schedule changes: substitution, swap, reschedule, cancellation, extra session.
9. Semester final grades: one final grade per Student × Subject × Semester.
10. Academic report card.
11. Academic history.
12. Academic transcript.
13. KPI/reporting semantic layer.
14. Data Quality and alert infrastructure.
15. Historical migration framework.
16. Audit, correction and versioning.

## Explicit MVP exclusions
- Detailed Tugas/Quiz/UTS/UAS assessment engine.
- Remedial engine.
- Competency scoring engine.
- GPA/IP/IPK.
- Student ranking.
- Composite “Academic Score”.
- Composite student risk score.
- AI-based grading or student risk decisions.
- Student profile photo dependency.
- Cross-domain parent development report.

## Shared/Core objects
- students
- student identifiers
- student status history
- staff/users/RBAC
- academic years/semesters
- locations
- academic calendar
- audit/correction
- migration infrastructure
- shared alert infrastructure

## Domain ownership
Academic owns Academic transactions. Do not create `academic_students`; Academic references canonical `students.id`.

Future domains such as Tahfizh and Kesantrian must not be forced into Academic transaction tables.
