# Academic-First Delivery Task Queue

**Scope:** Shared foundation required by Academic + Academic MVP delivery. Future domain modules use their own queues only after DESIGN_READY.

**Current execution state:** `PILOT_IMPLEMENTED_HARDENING / GOVERNANCE_GATE_PENDING`  
**Current canonical gate:** `SOC-MD-06` (management decision; not an implementation sprint)  
**Queue role:** records existing Academic implementation history; MVP/pilot implementation is substantially complete. Current work is governance/canonical hardening; no new source task is authorized by this rebase.

Status vocabulary: `READY`, `NOT_STARTED`, `IN_PROGRESS`, `DONE`, `BLOCKED_POLICY`, `BLOCKED_TECHNICAL`, `DEFERRED_FUTURE`.

## Sprint 0 — Foundation
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S0-001 Initialize Laravel/PostgreSQL workspace under `application/web/` | DONE | 100% | — | Runnable app/test harness/environment docs + repository/secret baseline |
| IMP-S0-002 Establish code/module + safe-change conventions | DONE | 100% | S0-001 | Module boundaries, Git/branch/change-ID, protected zones, migration immutability, Change Manifest/release conventions documented |
| IMP-S0-003 Add baseline CI/local verification + deployment-readiness workflow | DONE | 100% | S0-002 | Repeatable checks + staging/production promotion/rollback contract verification |

## Sprint 1 — Core / RBAC / Audit
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S1-001 Users, staff, org units, locations schema | DONE | 100% | S0-003 | Core migrations/models/tests |
| IMP-S1-002 Student + permanent `student_code` | DONE | 100% | S1-001 | Student master |
| IMP-S1-003 Student identifiers + correction/version/audit | DONE | 100% | S1-002 | NIS/NISN lifecycle |
| IMP-S1-004 Student status history | DONE | 100% | S1-002 | Effective-dated lifecycle |
| IMP-S1-005 RBAC roles/permissions/effective assignments/scopes | DONE | 100% | S1-001 | Backend authorization foundation |
| IMP-S1-006 Append-only audit + correction-request foundation | DONE | 100% | S1-003,S1-004,S1-005 | Shared governance services |
| IMP-S1-007 Guardian master + Student relationships + contact-channel baseline | DONE | 100% | S1-002,S1-006 | Canonical guardian/parent context |
| IMP-S1-008 Shared Core contracts + Core DQ/security contract tests | DONE | 100% | S1-001,S1-002,S1-003,S1-004,S1-005,S1-006,S1-007 | Stable cross-module Core foundation |

## Sprint 2 — Academic structure
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S2-001 Shared academic-year reference + Academic grade-level/class/subject master | DONE | 100% | S1-006 | Shared year reference + grade levels + year-specific class/rombel (`section_code` data-driven) + Academic master |
| IMP-S2-002 Effective-dated student class enrollment | DONE | 100% | S2-001 | Enrollment + overlap protection |
| IMP-S2-003 Effective-dated homeroom assignment + scope resolver | DONE | 100% | S2-001,S1-005 | Wali Kelas authority |
| IMP-S2-004 Academic calendar events | DONE | 100% | S2-001 | Holiday/non-teaching source |

## Sprint 3 — Teaching/schedule/session
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S3-001 Teaching assignments | DONE | 100% | S2-001 | Teacher×class×subject period assignment |
| IMP-S3-002 Schedule rules + WEEK_OF_MONTH + conflict/constraint engine | DONE | 100% | S3-001,S2-004 | Effective-date/recurrence teacher+class hard blocks, preflight + transactional recheck + concurrency guard |
| IMP-S3-003 Idempotent class-session generator with calendar check | DONE | 100% | S3-002,S2-002 | Session backbone |
| IMP-S3-004 Session student participant snapshots | DONE | 100% | S3-003 | Expected roster |
| IMP-S3-005 Extra/ad-hoc session creation | DONE | 100% | S3-003 | One-off/selected-student sessions through same teacher/class conflict engine |

## Sprint 4 — Schedule changes / teacher participation
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S4-001 Teacher participation/obligation structure | DONE | 100% | S3-003 | PRIMARY/SUBSTITUTE expectation model |
| IMP-S4-002 Substitution workflow | DONE | 100% | S4-001 | Audited substitution + replacement-teacher conflict validation |
| IMP-S4-003 Swap workflow | DONE | 100% | S4-001 | Atomic resulting-state conflict validation; no false absence |
| IMP-S4-004 Reschedule workflow | DONE | 100% | S3-003 | Target conflict validation before source/replacement lineage mutation |
| IMP-S4-005 Cancellation workflow | DONE | 100% | S3-003 | No attendance opportunity; approved cancellation releases future slot |
| IMP-S4-006 Official teacher attendance input/finalization | DONE | 100% | S4-001 | Wali Kelas records PRESENT/ABSENT; editable with audited correction |

## Sprint 5 — Student attendance
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S5-001 Attendance schema + Wali Kelas scoped draft entry | DONE | 100% | S3-004,S2-003 | DRAFT attendance workflow |
| IMP-S5-002 Atomic FinalizeStudentAttendance | DONE | 100% | S5-001 | VALIDATED attendance + session completion |
| IMP-S5-003 Permission reference consistency when present | DONE | 100% | S5-001 | Direct per-session IZIN; optional permission reference |
| IMP-S5-004 Responsive Wali Kelas attendance UI | DONE | 100% | S5-002 | Authorized responsive route/view with draft/finalize actions |
| IMP-S5-005 Attendance DQ completeness checks | DONE | 100% | S5-002 | Missing/unresolved detection; missing ≠ absent |

## Sprint 6 — Lock/correction/governance
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S6-001 Open-period attendance correction | DONE | 100% | S5-002,S1-006 | reason+audit+version |
| IMP-S6-002 Attendance period lock + prerequisites | DONE | 100% | S5-005 | Per-class calendar month; lock 15 days after month end by Waka |
| IMP-S6-003 Post-lock correction request/review/apply | DONE | 100% | S6-002 | Waka review/apply + audited Super Admin override |
| IMP-S6-004 Historical homeroom handover completion command | DONE | 100% | S6-001,S2-003 | audited exception authority |
| IMP-S6-005 Admin attendance exception/completeness view | DONE | 100% | S5-005,S6-002 | exception operations |

## Sprint 7 — Semester grades
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S7-001 Semester subject grade schema/API with provenance | DONE | 100% | S3-001,S2-002 | Student×Subject×Semester final score |
| IMP-S7-002 Grade completeness query/service | DONE | 100% | S7-001 | Expected vs available grades |
| IMP-S7-003 Grade correction audit/version foundation | DONE | 100% | S7-001,S1-006 | controlled correction structure |
| IMP-S7-004 Official grade finalization/lock workflow | DONE | 100% | S7-001 | Activate after workflow/grain/authority decision |

## Sprint 8 — Report Card / Transcript
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S8-001 Report-card readiness service | DONE | 100% | S6-002,S7-002 | readiness reasons |
| IMP-S8-002 Structured report-card draft/snapshot/versioning | DONE | 100% | S8-001 | immutable report model |
| IMP-S8-003 Homeroom report note workflow | DONE | 100% | S8-002,S2-003 | parent-facing approved note path |
| IMP-S8-004 Report review/approve/publish | DONE | 100% | S8-002 | final approver/signatory policy required |
| IMP-S8-005 Academic history view | DONE | 100% | S7-001 | longitudinal derived history |
| IMP-S8-006 Transcript draft/snapshot/versioning | DONE | 100% | S8-005 | structured transcript |
| IMP-S8-007 Transcript approve/publish | DONE | 100% | S8-006 | final authority/signatory policy required |

## Sprint 9 — KPI / reporting
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S9-001 Attendance semantic metrics | DONE | 100% | S6-002 | opportunity/count/presence/completeness |
| IMP-S9-002 Session metrics | DONE | 100% | S4-005 | completion/cancellation/extra |
| IMP-S9-003 Grade metrics | DONE | 100% | S7-002 | completeness/means/trends |
| IMP-S9-004 Role-specific operational/management views | DONE | 100% | S9-001,S9-002,S9-003 | Wali/Waka views |
| IMP-S9-005 Export through semantic services | DONE | 100% | S9-004 | no duplicate formulas |

## Sprint 10 — Alert infrastructure
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S10-001 Shared alert rules/alerts/status/actions infrastructure | DONE | 100% | S1-005 | auditable alert engine |
| IMP-S10-002 Deterministic Academic DQ/operational rules | DONE | 100% | S10-001,S6-005,S7-002 | active MVP rules |
| IMP-S10-003 Owner resolution/dedup/auto-resolution | DONE | 100% | S10-002 | actionable queue |
| IMP-S10-004 Student Early Warning thresholds | BLOCKED_POLICY | 0% | S10-001,S9-001 | activate only after approved thresholds |

## Sprint 11 — Migration
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S11-001 Import batch/file/row/error/mapping/lineage infrastructure | DONE | 100% | S1-006 | migration engine |
| IMP-S11-002 Dry-run, identity review and reconciliation | DONE | 100% | S11-001 | safe preview/accounting |
| IMP-S11-003 Legacy daily/monthly tables as source inventory requires | DONE | 100% | S11-002 | preserve actual grain only |
| IMP-S11-004 Controlled initial master/history migration | DONE | 100% | S11-002 | reconciled accepted batches |

## Sprint 12 — Hardening / UAT / pilot
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| IMP-S12-001 Full P0/P1 regression and negative RBAC suite | DONE | 100% | S5-S11 relevant | verified core integrity |
| IMP-S12-002 Concurrency/idempotency/atomicity hardening | DONE | 100% | S12-001 | retry-safe workflows |
| IMP-S12-003 Performance baseline | DONE | 100% | S12-001 | realistic load evidence |
| IMP-S12-004 Backup + restore test | DONE | 100% | S12-001 | recovery evidence |
| IMP-S12-005 Business UAT evidence | DONE | 100% | S12-001 | signed acceptance/defect register |
| IMP-S12-006 Controlled 1–2 class pilot | DONE | 100% | S12-002,S12-004,S12-005 | pilot evidence |
| IMP-S12-007 Production cutover readiness review | NOT_STARTED | 0% | S12-006 | Gate G decision |

## Future — explicitly deferred
Detailed Tugas/Quiz/UTS/UAS assessment engine, remedial, mastery/KKM unless approved, rank/composite scores, shared AI-native layer, profile photo, cross-domain parent development report, and microservices are `DEFERRED_FUTURE`.

## Future AI Layer — explicitly deferred and excluded from current implementation progress
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| FUT-AI-001 Implement thin AIProvider boundary + selected OpenAI API adapter/secrets/feature flags + AI RBAC permission catalog/default grants | DEFERRED_FUTURE | 0% | Stable auth/audit foundation + explicit user authorization | Optional AI platform foundation; `SUPER_ADMIN` only initial access |
| FUT-AI-002 Implement read-only Academic AI chat/tools as Super Admin pilot | DEFERRED_FUTURE | 0% | FUT-AI-001 + stable Academic semantic services | Authorized grounded Q&A; all other roles deny-by-default |
| FUT-AI-003 Add AI interaction/tool/usage observability + eval suite | DEFERRED_FUTURE | 0% | FUT-AI-002 | Auditable/tested AI operation |
| FUT-AI-004 Implement structured draft assistant | DEFERRED_FUTURE | 0% | Relevant domain workflows stable | Preview-only drafts |
| FUT-AI-005 Enable explicitly whitelisted controlled action tools | DEFERRED_FUTURE | 0% | FUT-AI-004 + confirmation/idempotency/evals | AI-assisted domain commands |
| FUT-AI-006 Expand shared assistant to Tahfizh/Kesantrian/other domains | DEFERRED_FUTURE | 0% | Domains production-ready + cross-domain RBAC/privacy | Shared IMTAQ assistant |



## Future Parent Portal — explicitly deferred and excluded from current implementation progress
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| FUT-PORTAL-001 Implement Guardian↔User account linking, activation/suspension foundation | DEFERRED_FUTURE | 0% | IMP-S1-007 + IMP-S1-008 + stable Auth/RBAC/Audit | Portal identity bridge |
| FUT-PORTAL-002 Implement `PARENT_GUARDIAN` role + `OWN_AUTHORIZED_CHILDREN` resolver | DEFERRED_FUTURE | 0% | FUT-PORTAL-001 + approved historical-access policy | Child-scoped backend authorization |
| FUT-PORTAL-003 Implement Academic published-report read-only Portal | DEFERRED_FUTURE | 0% | FUT-PORTAL-002 + IMP-S8-004 stable publication | Authorized child/report list + exact report view |
| FUT-PORTAL-004 Implement secure document view/download path + access logging | DEFERRED_FUTURE | 0% | FUT-PORTAL-003 + download/session policy | Secure artifact delivery |
| FUT-PORTAL-005 Integrate Shared Communication notification/deep links | DEFERRED_FUTURE | 0% | FUT-PORTAL-003 + FUT-COMM-003 | Notification→Portal navigation without auth bypass |
| FUT-PORTAL-006 Security hardening + Guardian UAT pilot | DEFERRED_FUTURE | 0% | FUT-PORTAL-004 | IDOR/session/privacy/UAT evidence |
| FUT-PORTAL-007 Expand to approved Tahfizh/Kesantrian/cross-domain parent artifacts | DEFERRED_FUTURE | 0% | source modules production-ready + explicit parent contracts | Multi-domain Parent Portal |
| FUT-PORTAL-008 Optional read-only Parent AI grounded only in parent-visible content | DEFERRED_FUTURE | 0% | FUT-PORTAL-006 + stable Shared AI + AI privacy/evals | Parent AI assistant |

## Future Shared Communication / WhatsApp — explicitly deferred and excluded from current implementation progress
| Task | Status | Progress | Depends on | Deliverable |
|---|---|---:|---|---|
| FUT-COMM-001 Integrate Shared Core Guardian + Staff/Organization recipient context with communication authorization/consent policy | DEFERRED_FUTURE | 0% | IMP-S1-007 + IMP-S1-008 + approved ownership/privacy policy | Recipient/audience eligibility integration |
| FUT-COMM-002 Implement verified contact channels, consent/preferences and channel eligibility | DEFERRED_FUTURE | 0% | FUT-COMM-001 + approved channel/consent policy | Eligible channel resolution |
| FUT-COMM-003 Implement provider-neutral communication core: purpose, templates, batches, messages, queue, audit/idempotency | DEFERRED_FUTURE | 0% | FUT-COMM-002 + stable jobs/audit | Shared communication engine supporting ANNOUNCEMENT/REMINDER/ACTION_REQUEST |
| FUT-COMM-004 Implement audience resolver + managed communication groups + scoped recipient preview | DEFERRED_FUTURE | 0% | FUT-COMM-001 + approved audience/group ownership policy | Institutional audience/group registry |
| FUT-COMM-005 Implement scheduled reminder framework from official domain events/schedules | DEFERRED_FUTURE | 0% | FUT-COMM-003 + FUT-COMM-004 | Idempotent event/schedule reminders |
| FUT-COMM-006 Implement structured ACTION_REQUEST + response lifecycle | DEFERRED_FUTURE | 0% | FUT-COMM-003 + inbound-response policy | Structured request/response platform |
| FUT-COMM-007 Implement WhatsApp provider adapter + backend secrets + verified delivery/response webhook normalization | DEFERRED_FUTURE | 0% | FUT-COMM-003 + FUT-COMM-006 + current external provider-policy verification | WhatsApp transport + approved structured reply support |
| FUT-COMM-008 Implement Academic teacher availability / Tomorrow Readiness pilot | DEFERRED_FUTURE | 0% | FUT-COMM-005 + FUT-COMM-006 + stable IMP-S3/S4 teacher obligations + approved timing/escalation policy | CONFIRMED/UNAVAILABLE/NO_RESPONSE readiness workflow |
| FUT-COMM-009 Add internal announcement/broadcast pilot for approved Academic audiences | DEFERRED_FUTURE | 0% | FUT-COMM-004 + FUT-COMM-007 + approved sender/audience policy | Staff broadcast with scoped recipient preview/audit |
| FUT-COMM-010 Implement Academic published-report personalized Guardian delivery pilot | DEFERRED_FUTURE | 0% | FUT-COMM-007 + stable published Academic reports | Parent-specific report delivery |
| FUT-COMM-011 Add delivery/response dashboard, retry, no-response/exception handling and operational alerts | DEFERRED_FUTURE | 0% | FUT-COMM-008 + FUT-COMM-010 | Observable/recoverable communication operations |
| FUT-COMM-012 Expand to Tahfizh/Kesantrian/Administratif and other approved audiences/artifacts | DEFERRED_FUTURE | 0% | source modules production-ready + explicit communication contracts | Multi-domain institutional + parent communication |
