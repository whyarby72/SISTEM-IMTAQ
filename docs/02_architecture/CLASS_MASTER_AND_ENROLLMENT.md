# Academic Class Master & Enrollment Architecture v1.0

**Status:** DESIGN_LOCKED  
**Scope:** Academic class structure, grade level, rombel/section, yearly class instances, student enrollment and homeroom context.

## 1. Purpose
SISTEM IMTAQ must support class structures that can change between academic years without code changes, for example:

```text
Tingkat 1 → Kelas 1 A, 1 B
Tingkat 2 → Kelas 2 A, 2 B
Tingkat 3 → Kelas 3 A, 3 B
```

and later:

```text
Tingkat 1 → 1 A, 1 B, 1 C
Tingkat 2 → 2 A, 2 B
Tingkat 3 → 3 A, 3 B, 3 C, 3 D
```

`A/B/C/...` are data, not hard-coded application enums.

## 2. Canonical model

```text
Academic Year
    ↓
Grade Level
    ↓
Year-Specific Class / Rombel
    ↓
Student Class Enrollment
    ↓
Homeroom / Teaching Assignment / Schedule / Session
```

### `grade_levels`
Represents the academic level independently from a particular rombel.

Recommended fields:
- `id UUID PK`
- `organizational_unit_id FK nullable/according to unit model`
- `level_code varchar` — e.g. `1`, `2`, `3`
- `display_name varchar` — e.g. `Tingkat 1`
- `sequence_no integer`
- `status` — `ACTIVE/INACTIVE`
- audit metadata

Do not encode class sections inside `grade_levels`.

### `classes`
One row represents one class/rombel in one academic year.

Recommended fields:
- `id UUID PK`
- `class_code varchar UNIQUE`
- `academic_year_id FK`
- `organizational_unit_id FK`
- `grade_level_id FK`
- `section_code varchar` — e.g. `A`, `B`, `C`; configurable data, not enum
- `display_name varchar` — e.g. `Kelas 1 A`
- `status` — `ACTIVE/INACTIVE/CLOSED` according to implementation policy
- audit metadata

Recommended uniqueness at the active business level:

```text
(academic_year_id, organizational_unit_id, grade_level_id, section_code)
```

A class is year-specific. `Kelas 1 A` in 2026/2027 and `Kelas 1 A` in 2027/2028 are different `class_id` records even if the display label is the same.

## 3. Student enrollment
`students` never stores class as permanent identity.

Use effective-dated `student_class_enrollments`:

```text
Student STU-0017
2026/2027 → Kelas 1 A
2027/2028 → Kelas 2 B
2028/2029 → Kelas 3 A
```

A mid-year transfer closes the old enrollment and opens a new one. Historical sessions, attendance, grades and reports retain the historical class context.

## 4. Homeroom and teaching assignments
`class_homeroom_assignments`, `teaching_assignments`, `schedule_rules` and `class_sessions` reference `class_id`.

No canonical field such as `students.current_class_name` or `classes.homeroom_staff_id` may replace effective-dated relationships.

## 5. Reporting
The semantic/reporting layer must support aggregation by:
- one rombel/class (`1 A`),
- grade level (`Tingkat 1 = 1 A + 1 B + ...`),
- organizational unit,
- academic year.

Do not parse `display_name` to infer grade level or section.

## 6. Annual changes
Adding/removing a rombel in a new year is configuration/data work, not source-code work.

Example:

```text
2026/2027: 1 A only
2027/2028: 1 A + 1 B
2028/2029: 1 A + 1 B + 1 C
```

The application must require no new enum/redeployment solely because a new section code appears.

## 7. Promotion
Promotion is not `UPDATE students.class_id`.

Future promotion workflow creates/updates next-year enrollments while preserving prior enrollment history. A batch promotion feature may be added later without changing the canonical model.

## 8. Guardrails
- Class names are display labels, not primary keys.
- Section codes are configurable data, not hard-coded `A/B` choices.
- Grade level and section are separate concepts.
- Classes are academic-year-specific.
- Student identity is independent from class placement.
- Changing class structure never creates a new Student_ID.
- No sheet/table per class is used as a database pattern.

## 9. UAT invariants
1. Create `1 A` and `1 B` under the same grade level and year → both valid.
2. Create `1 C` later → no code/schema change required.
3. Duplicate `1 A` in the same year/unit/grade level → blocked.
4. `1 A` in a different academic year → valid new class record.
5. Student moves `1 A → 1 B` mid-year → history retained.
6. Historical attendance before transfer remains attached to the prior class/session.
7. Reporting `Tingkat 1` aggregates all active/relevant sections using `grade_level_id`, not text parsing.
8. New-year removal of section B does not delete historical `1 B`.

## 10. Implementation ownership
This contract is implemented within Academic Structure (Sprint 2), primarily task `IMP-S2-001`, and consumed by enrollment, homeroom, schedule/session, attendance, grades and reporting.
