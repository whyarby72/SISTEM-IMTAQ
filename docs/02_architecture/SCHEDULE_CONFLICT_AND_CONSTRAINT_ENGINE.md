# Academic Schedule Conflict & Constraint Engine v1.0

**Status:** `DESIGN_LOCKED`  
**Module:** Academic  
**Change class:** `MODULE_INTERNAL` with PostgreSQL integrity implications  
**Implementation tasks:** `IMP-S3-002`, `IMP-S3-003`, `IMP-S3-005`, `IMP-S4-002`–`IMP-S4-005`

## 1. Purpose
Guarantee that an effective Academic timetable cannot contain physically impossible teaching obligations, especially one teacher assigned to overlapping classes. The engine is part of the canonical schedule/session write path, not a UI-only warning.

Core invariant:

> One Staff_ID may not be an effective delivery teacher for more than one Academic session during overlapping time intervals.

The same engine also protects class occupancy and, when institutional policy activates it, exclusive location occupancy.

## 2. Decision Gate
| Item | Design |
|---|---|
| Domain / goal | Academic scheduling integrity and operational readiness |
| Process owner | Waka Akademik / authorized Academic scheduling authority |
| Users | Admin/PIC Academic and authorized schedule-change actors |
| Source of Truth | `teaching_assignments` + effective `schedule_rules` + `class_sessions` + applied `schedule_changes` + `session_teacher_participations` |
| Smallest conflict grain | Resource × concrete time occurrence |
| Inputter | Authorized Academic scheduler/change actor |
| Validator | Deterministic system conflict engine |
| Approver | Schedule-change approver remains policy-dependent where already registered |
| Lifecycle | Candidate/preflight → authoritative recheck → persist/apply or reject |
| Audit | Accepted changes and rejected high-value conflict attempts are traceable |
| KPI/DQ | Active schedule conflicts = 0; detected imported/integration conflict = HIGH DQ |
| AI | Not required. AI may never bypass the conflict engine. |

## 3. Canonical time semantics
Use half-open intervals:

`[start_at, end_at)`

Two intervals overlap when:

`candidate_start < existing_end AND candidate_end > existing_start`

Therefore:
- `08:00–09:30` and `09:00–10:00` overlap → block.
- `08:00–09:30` and `09:30–11:00` touch but do not overlap → allowed by the base engine.
- Any future transition/travel buffer is a configurable policy, not developer-invented logic.

All persisted schedule/session intervals must satisfy `start < end`.

## 4. Resource conflict types
### 4.1 `TEACHER_CONFLICT` — HARD BLOCK
Trigger when the same `staff_id` is an effective delivery teacher for two different active occurrences whose intervals overlap.

Applies to:
- recurring schedule creation/update;
- generated class sessions;
- extra/ad-hoc sessions;
- substitution replacement teacher;
- swap resulting teachers;
- reschedule/time-change result;
- any future scheduling write path.

A conflict is resolved by changing the teacher/time/session state through the correct workflow. There is no normal `ignore conflict` bypass.

### 4.2 `CLASS_CONFLICT` — HARD BLOCK
The same `class_id` may not have two active Academic class sessions whose intervals overlap.

This remains true even when teachers differ. A valid exception must be represented by a different approved business model, not by saving contradictory sessions.

### 4.3 `LOCATION_CONFLICT` — POLICY-CONTROLLED
If a location is configured as exclusive, overlapping active sessions in the same `location_id` are blocked. Exact location exclusivity/transition rules remain policy/configuration and must not be guessed.

## 5. What counts as an active/effective occurrence
### Schedule-rule level
A candidate rule conflicts only when all are true:
1. effective date ranges intersect;
2. the canonical recurrence resolver produces at least one common concrete date in that intersection;
3. time-of-day intervals overlap on that date;
4. teacher/class resource identity matches the relevant conflict type.

The conflict engine must reuse the same recurrence semantics as session generation. It must not maintain a second interpretation of `EVERY_WEEK`, `WEEK_OF_MONTH`, `ODD_WEEK`, or `EVEN_WEEK`.

### Class-session level
Conflict-blocking session states are normally:
- `PLANNED`
- `CONFIRMED`
- `COMPLETED` for historical/backdated integrity checks

Non-blocking source states:
- `CANCELLED`
- `RESCHEDULED` source session after the replacement is validly created/applied

A source session marked `RESCHEDULED` must not continue reserving its old delivery slot.

## 6. Effective teacher resolution
Conflict checking is based on the teacher(s) expected to physically deliver the resulting session, while preserving obligation history.

- Normal session: PRIMARY teacher blocks its interval.
- Applied SUBSTITUTION: substitute becomes the effective delivery teacher for conflict purposes; original responsibility/obligation lineage remains preserved for teacher-attendance/audit semantics.
- Applied SWAP: validate the complete resulting teacher assignment for both affected sessions before either side is committed.
- RESCHEDULE/TIME_CHANGE: validate the teacher against the replacement/new interval.
- EXTRA/AD_HOC: validate every effective delivery teacher before creation.

No schedule-change command may partially apply if any resulting teacher/class conflict exists.

## 7. Two-stage validation
### Stage A — Preflight
UI/API may call a read-only conflict check while the user is editing. It returns actionable conflicts but is advisory because data may change before save.

Suggested service:

`CheckAcademicScheduleConflicts(candidate)`

Response should contain structured items such as:
- `conflict_type`
- candidate resource/session/rule refs
- conflicting rule/session ref
- teacher/class/location identity
- concrete overlap date/time
- blocking severity
- human-readable resolution context

### Stage B — Authoritative write check
Every persistence/apply command re-runs conflict validation inside the same database transaction immediately before mutation. Never trust a previous browser preflight result.

Required callers include:
- Create/Update/ActivateScheduleRule
- GenerateClassSessions
- CreateExtraSession
- ApplySubstitution
- ApplySwap
- ApplyReschedule / ApplyTimeChange
- any future bulk timetable import/activation command

## 8. Concurrency protection
Frontend validation alone is insufficient. Two concurrent requests must not both pass stale reads and create a double booking.

For PostgreSQL MVP, use transaction-scoped resource serialization (for example `pg_advisory_xact_lock` or an equivalent deterministic locking strategy) plus a final overlap query.

Lock rules:
1. acquire locks for all affected resource IDs (`TEACHER`, `CLASS`, and policy-enabled `LOCATION`);
2. acquire in deterministic order to reduce deadlocks;
3. re-query overlaps after locks are held;
4. persist/apply only when the result is conflict-free;
5. commit atomically.

Swap and other multi-resource changes lock all affected teachers/classes before revalidation and application.

## 9. PostgreSQL defensive integrity
Use database protection where the resource exists on the same row.

For `class_sessions`, PostgreSQL `btree_gist` can support an exclusion constraint equivalent to:

```sql
EXCLUDE USING gist (
  class_id WITH =,
  tstzrange(planned_start_at, planned_end_at, '[)') WITH &&
)
WHERE (session_status IN ('PLANNED','CONFIRMED','COMPLETED'));
```

Exact migration syntax must be verified against the target PostgreSQL version.

Do **not** denormalize `teacher_staff_id` into `class_sessions` merely to create an exclusion constraint; teacher delivery is intentionally modeled through `session_teacher_participations` and schedule-change semantics. Teacher concurrency is protected through the domain service + transaction locking + DQ detection.

## 10. Recurrence conflict evaluation
For recurring rules, symbolic comparison must not drift from session generation. Preferred MVP approach:
1. intersect effective date ranges;
2. ask the canonical occurrence resolver for concrete candidate dates within that finite Academic period;
3. compare teacher/class resources and `[start,end)` times only on dates where both rules occur;
4. return the earliest conflict plus optional additional occurrences.

This supports variable schedules and `WEEK_OF_MONTH` without hard-coding institution-wide slots.

Calendar blocking is still evaluated by the session generator. Schedule-rule conflict checking must remain conservative enough that a future calendar change cannot silently create a teacher double booking; if calendar-aware exception handling is later required, it needs an explicit policy/ADR.

## 11. Schedule-change atomicity
### Substitution
Before applying, validate the replacement teacher against the source session interval. If the replacement conflicts, reject the substitution and leave the source unchanged.

### Swap
Compute both resulting assignments first. Validate both results as one unit. Apply both or neither.

### Reschedule / time change
Validate the replacement/new interval before marking the source as rescheduled/applied. A failed target must leave canonical source state unchanged.

### Cancellation
A valid cancellation releases the future delivery slot only through the approved cancellation workflow; deleting the session is prohibited.

### Extra/ad-hoc session
Must pass teacher/class conflict checks exactly like scheduled sessions.

## 12. Data Quality backstop
Prevention is primary; detection is still mandatory for imported data, defects, direct integration errors, or legacy anomalies.

`ACA_DQ_SCHEDULE_CONFLICT` must detect at least:
- effective teacher overlap;
- effective class overlap;
- exclusive-location overlap when policy enabled.

Recommended severity: `HIGH`; escalate to `CRITICAL` only by approved operational/security rule.

Alert evidence should identify both conflicting facts and the exact overlap interval. Repeated evaluation deduplicates the same active conflict.

## 13. Error/UX contract
A hard conflict must return a structured business error, not a generic database failure. Example:

```text
Tidak dapat menyimpan jadwal.
Ustadz Ahmad sudah memiliki kewajiban mengajar:
Fiqih — Kelas 1 A — Senin 08:00–09:30.
Jadwal yang diajukan:
Nahwu — Kelas 1 B — Senin 09:00–10:30.
Bentrok: 09:00–09:30.
```

UI may visualize the collision, but backend enforcement is authoritative.

## 14. Audit expectations
Persisted schedule changes already follow normal audit/versioning. Additionally record enough metadata for rejected authoritative conflict attempts to support troubleshooting, without turning every keystroke/preflight into a business transaction.

Minimum useful evidence:
- actor/user;
- command/action;
- candidate resource refs;
- conflict type;
- conflicting entity ref;
- overlap interval;
- timestamp/request correlation ID.

## 15. Policy pending — do not hard-code
- minimum transition/travel buffer between sessions;
- whether/how specific locations are exclusive resources;
- any calendar-aware exception that would permit otherwise overlapping recurring rules;
- exact approver roles already listed for substitution/swap/reschedule/cancellation/extra session.

Base rule without approved buffer: exact endpoint adjacency is allowed.

## 16. Acceptance invariants
- `SCHED-CF-001` one teacher cannot be effective delivery teacher in overlapping sessions.
- `SCHED-CF-002` one class cannot have overlapping active sessions.
- `SCHED-CF-003` exact endpoint adjacency is not an overlap unless future buffer policy says otherwise.
- `SCHED-CF-004` effective dates and recurrence occurrences are evaluated, not just weekday labels.
- `SCHED-CF-005` substitute teacher is conflict-checked before apply.
- `SCHED-CF-006` swap validates the complete resulting state atomically.
- `SCHED-CF-007` reschedule/time change validates target before source mutation.
- `SCHED-CF-008` extra/ad-hoc sessions use the same conflict engine.
- `SCHED-CF-009` final save rechecks inside a protected transaction; UI preflight is never sufficient.
- `SCHED-CF-010` DQ periodically detects conflicts that bypass prevention.
- `SCHED-CF-011` no normal role has an `ignore teacher conflict` bypass.
- `SCHED-CF-012` AI/Communication cannot bypass or mutate around schedule conflict rules.
