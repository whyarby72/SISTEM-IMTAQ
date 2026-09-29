# Academic Wali Kelas Pilot Provisioning Verification

**Task:** `ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION`  
**Mode:** `VERIFY PILOT READ-ONLY ONLY`  
**Branch baseline:** `17922f48ac2ddd10b1cd3f473c975b36d0ab2692`  
**Business date:** `2026-09-30` (`Asia/Jakarta`)  
**Verification horizon:** `[2026-09-30 00:00, 2026-10-07 00:00)` Asia/Jakarta  
**Final decision:** `PROVISIONING_GAP_FOUND`

## Executive verification decision

The intended local PILOT environment was proven and inspected in one guarded
PostgreSQL read-only transaction. The five active official Wali-operated
classes have effective Wali authority and active aggregate rosters, but pilot
readiness is not complete:

- all reportable sessions found for classes 1, 2A, 2B, and 3A lack an expected
  primary teacher participation record;
- official class `IMTAQ-2026-3B` has no usable schedule rule and no session in
  the seven-day horizon.

Therefore the exact decision is `PROVISIONING_GAP_FOUND`. No gap was fixed in
this task.

## Environment identity evidence

Identity was checked before any business-data query. Recorded evidence is
limited to non-secret markers:

- repository branch: `chore/academic-wali-pilot-provisioning-verification`;
- application environment marker: `APP_ENV=local`;
- connection marker: `DB_CONNECTION=pgsql`;
- PostgreSQL current database: `imtaq`;
- PostgreSQL major: `18`;
- session timezone: `Asia/Jakarta`;
- the database identity matches the repository's established local PILOT
  identity lineage; no staging or production target was used.

No host credential, password, connection string, email, username, token, or
secret was recorded.

## Read-only transaction proof

The connection was opened with `BEGIN TRANSACTION READ ONLY` before business
queries. `SHOW transaction_read_only` returned `on`. All provisioning checks
used aggregate `SELECT` queries only. The transaction was rolled back at the
end. No insert, update, delete, migration, seed, session generation,
attendance entry, correction, login side effect, or configuration mutation was
performed.

## Per-class readiness matrix

The matrix contains one row per current official Wali-operated class. Student
names and student-level rows are intentionally excluded.

| Class | Wali authority | Active roster | Schedule availability | Session availability (7d) | Participant snapshot | Teacher participation | Lock state | Final readiness |
|---|---|---:|---|---|---|---|---|---|
| `IMTAQ-2026-1` | PASS | 20 | PASS (10 usable rules) | PASS (11 sessions, 10 reportable) | PASS (11/11 sessions with participants) | GAP (0/11 primary expected) | OPEN | PROVISIONING_GAP_FOUND |
| `IMTAQ-2026-2A` | PASS | 19 | PASS (12 usable rules) | PASS (12/12 reportable) | PASS (12/12 sessions with participants) | GAP (0/12 primary expected) | OPEN | PROVISIONING_GAP_FOUND |
| `IMTAQ-2026-2B` | PASS | 10 | PASS (14 usable rules) | PASS (12/12 reportable) | PASS (12/12 sessions with participants) | GAP (0/12 primary expected) | OPEN | PROVISIONING_GAP_FOUND |
| `IMTAQ-2026-3A` | PASS | 15 | PASS (14 usable rules) | PASS (14/14 reportable) | PASS (14/14 sessions with participants) | GAP (0/14 primary expected) | OPEN | PROVISIONING_GAP_FOUND |
| `IMTAQ-2026-3B` | PASS | 20 | GAP (0 usable rules) | GAP (0 sessions) | NOT_APPLICABLE | NOT_APPLICABLE | OPEN | PROVISIONING_GAP_FOUND |

`OPEN` means no current-date attendance-period lock row with `LOCKED` status
was found for the class. It does not override the other provisioning gaps.

## Identity, role, and homeroom findings

All five official classes have one active effective homeroom assignment, an
active roster, and an effective Wali role/user linkage in the pilot query.
The query used anonymized staff references only. No active official class was
orphaned from a Wali authority, and no duplicate active homeroom assignment
was returned for these five class rows.

The historical pilot-labelled class rows were not treated as official
operational classes. The verification matrix is limited to active official
class codes `IMTAQ-2026-1`, `IMTAQ-2026-2A`, `IMTAQ-2026-2B`,
`IMTAQ-2026-3A`, and `IMTAQ-2026-3B`.

## Academic structure and roster

The active roster aggregates are 20, 19, 10, 15, and 20 respectively. These
are counts only. No student names, identifiers, emails, phone numbers, or
student-level attendance facts were selected or recorded.

## Schedule and session findings

The horizon was evaluated using Jakarta business boundaries. Classes 1, 2A,
2B, and 3A have usable approved/published schedule rules and generated
sessions. Class 1 has 11 sessions, of which 10 are reportable because one is
cancelled; the other three classes have 12, 12, and 14 reportable sessions.

Official class 3B has no usable schedule rule and no generated session in the
horizon. This is a provisioning gap, not a reason to generate sessions during
this read-only task.

## Participant snapshot findings

Every available session in the first four classes has participant rows in the
snapshot relation. The aggregate required participant row counts are 220 for
class 1, 228 for 2A, 360 for 2B, and 210 for 3A. The 3B snapshot is not
applicable because no session exists in the verification horizon.

## Teacher participation findings

No current-horizon session in classes 1, 2A, 2B, or 3A has an expected
`PRIMARY` teacher participation row. This is the first shared operational
blocker for the available sessions and prevents claiming full controlled-pilot
readiness. No teacher participation record was created.

## Lock, correction, and escalation authority

No current-date class has an unexpected locked attendance-period row. One
effective Waka Akademik user with linked staff identity was found in aggregate.
The application-level correction/review authority was already evidenced by
the preceding readiness review; this verification did not execute or mutate a
correction workflow.

## Provisioning data-quality gaps

1. **Primary teacher participation gap:** 0 expected primary participation
   rows for all 49 available current-horizon sessions across classes 1, 2A,
   2B, and 3A.
2. **Class 3B schedule/session gap:** no usable schedule rule and no generated
   session for the official 3B class in the seven-day horizon.

## Smallest recommended remediation task

`ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS`

Provision and verify the expected primary teacher participation records for the
current reportable sessions of one selected official class only, then rerun
this read-only verification. Do not fix that task here. After that dependency
is cleared, separately provision/verify the official 3B schedule/session gap.

## Privacy and safety statement

This artifact records only class references, aggregate counts, anonymized staff
references, state labels, and non-secret environment markers. No secrets,
credentials, emails, usernames, phone numbers, student names, or
student-level records are included.

## Production-readiness separation

This result concerns only local PILOT provisioning. It does not imply staging
or production readiness. Deployment, backup/restore, secrets management,
monitoring, rollback, release approval, and production migration gates remain
separate and unassessed. Public Academic AI remains OFF and was not involved.

## Closeout

| Field | Result |
|---|---|
| Target environment | PILOT proven |
| PostgreSQL transaction | READ ONLY verified (`on`) |
| Final decision | `PROVISIONING_GAP_FOUND` |
| Data mutation | NONE |
| Recommended next task | `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS` |
| Production readiness | NOT ASSESSED |
| Implementation authorization | NOT AUTHORIZED |

