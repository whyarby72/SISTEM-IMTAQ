-- SOC-I0 aggregate read-only profile. No row contents or PII are selected.
-- Execute only inside a read-only transaction and ROLLBACK.

SELECT 'REPOSITORY_RUNTIME_SCHEMA_TABLES' AS metric, string_agg(table_name, ',' ORDER BY table_name) AS value
FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE';

SELECT 'DATABASE_APPLIED_MIGRATION_COUNT' AS metric, count(*)::text AS value FROM migrations;
SELECT 'DATABASE_LATEST_MIGRATION' AS metric, coalesce(max(migration),'NONE') AS value FROM migrations;
SELECT 'DATABASE_LATEST_BATCH' AS metric, coalesce(max(batch),0)::text AS value FROM migrations;
SELECT 'DATABASE_APPLIED_MIGRATION_NAMES' AS metric, coalesce(string_agg(migration, ',' ORDER BY id),'NONE') AS value FROM migrations;

SELECT 'CLASS_SESSION_TOTAL' AS metric, count(*)::text AS value FROM class_sessions;
SELECT 'SESSION_PLANNED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE session_status='PLANNED';
SELECT 'SESSION_CONFIRMED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE session_status='CONFIRMED';
SELECT 'SESSION_COMPLETED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE session_status='COMPLETED';
SELECT 'SESSION_CANCELLED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE session_status='CANCELLED';
SELECT 'SESSION_RESCHEDULED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE session_status='RESCHEDULED';
SELECT 'SESSION_INVALID_STATUS_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE session_status IS NULL OR session_status NOT IN ('PLANNED','CONFIRMED','COMPLETED','CANCELLED','RESCHEDULED');
SELECT 'SESSION_MIN_PLANNED_START' AS metric, coalesce(min(planned_start_at)::text,'NONE') AS value FROM class_sessions;
SELECT 'SESSION_MAX_PLANNED_START' AS metric, coalesce(max(planned_start_at)::text,'NONE') AS value FROM class_sessions;
SELECT 'PAST_PLANNED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE planned_start_at < current_timestamp AND session_status='PLANNED';
SELECT 'PAST_CONFIRMED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE planned_start_at < current_timestamp AND session_status='CONFIRMED';
SELECT 'FUTURE_COMPLETED_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE planned_start_at >= current_timestamp AND session_status='COMPLETED';

SELECT 'STUDENT_ATTENDANCE_TOTAL' AS metric, count(*)::text AS value FROM student_attendance;
SELECT 'LEGACY_EXCUSED_COUNT' AS metric, count(*)::text AS value FROM student_attendance WHERE attendance_status='EXCUSED';
SELECT 'UNEXPECTED_ATTENDANCE_STATUS_COUNT' AS metric, count(*)::text AS value FROM student_attendance WHERE attendance_status IS NOT NULL AND attendance_status NOT IN ('PRESENT','ABSENT','SICK','IZIN','LATE','EXCUSED','OTHER');
SELECT 'STUDENT_ATTENDANCE_STATUS_DISTRIBUTION' AS metric, coalesce(string_agg(coalesce(attendance_status,'NULL') || '=' || n::text, ',' ORDER BY coalesce(attendance_status,'NULL')),'EMPTY') AS value FROM (SELECT attendance_status,count(*) n FROM student_attendance GROUP BY attendance_status) s;

SELECT 'PLANNED_WITH_ATTENDANCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='PLANNED' AND EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);
SELECT 'CONFIRMED_WITH_ATTENDANCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='CONFIRMED' AND EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);
SELECT 'COMPLETED_WITH_ATTENDANCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='COMPLETED' AND EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);
SELECT 'COMPLETED_WITHOUT_ATTENDANCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='COMPLETED' AND NOT EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);
SELECT 'CANCELLED_WITH_ATTENDANCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='CANCELLED' AND EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);
SELECT 'RESCHEDULED_WITH_ATTENDANCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='RESCHEDULED' AND EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);

SELECT 'CANCELLED_WITH_EFFECTIVE_ATTENDANCE_ANOMALY_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='CANCELLED' AND EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);
SELECT 'CANCELLED_WITHOUT_REASON_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='CANCELLED' AND NOT EXISTS (SELECT 1 FROM schedule_changes sc WHERE sc.change_type='CANCELLATION' AND (sc.source_session_id=cs.id OR sc.related_session_id=cs.id) AND nullif(trim(sc.reason),'') IS NOT NULL);

SELECT 'RESCHEDULED_SOURCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE session_status='RESCHEDULED';
SELECT 'RESCHEDULED_REPLACEMENT_COUNT' AS metric, count(*)::text AS value FROM class_sessions WHERE rescheduled_from_session_id IS NOT NULL;
SELECT 'RESCHEDULE_LINEAGE_BROKEN_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='RESCHEDULED' AND NOT EXISTS (SELECT 1 FROM class_sessions replacement WHERE replacement.rescheduled_from_session_id=cs.id);
SELECT 'RESCHEDULED_SOURCE_WITH_ATTENDANCE_COUNT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE cs.session_status='RESCHEDULED' AND EXISTS (SELECT 1 FROM session_student_participants p JOIN student_attendance a ON a.session_student_participant_id=p.id WHERE p.class_session_id=cs.id);

SELECT 'JOINT_SESSION_COUNT' AS metric, count(*)::text AS value FROM (SELECT class_session_id FROM class_session_groups GROUP BY class_session_id HAVING count(*) > 1) j;
SELECT 'MAX_GROUPS_PER_SESSION' AS metric, coalesce(max(n),0)::text AS value FROM (SELECT class_session_id,count(*) n FROM class_session_groups GROUP BY class_session_id) j;
SELECT 'JOINT_OCCURRENCE_GRAIN_CONFLICT_COUNT' AS metric, 'NOT_DERIVABLE' AS value;

SELECT 'SESSION_PARTICIPANT_TOTAL' AS metric, count(*)::text AS value FROM session_student_participants;
SELECT 'SESSIONS_WITH_PARTICIPANT_SNAPSHOT' AS metric, count(DISTINCT class_session_id)::text AS value FROM session_student_participants;
SELECT 'SESSIONS_WITHOUT_PARTICIPANT_SNAPSHOT' AS metric, count(*)::text AS value FROM class_sessions cs WHERE NOT EXISTS (SELECT 1 FROM session_student_participants p WHERE p.class_session_id=cs.id);
SELECT 'DUPLICATE_SESSION_PARTICIPANT_COUNT' AS metric, coalesce(sum(n-1),0)::text AS value FROM (SELECT class_session_id,student_id,count(*) n FROM session_student_participants GROUP BY class_session_id,student_id HAVING count(*) > 1) d;

SELECT 'TEACHER_PARTICIPATION_TOTAL' AS metric, count(*)::text AS value FROM session_teacher_participations;
SELECT 'PRIMARY_PARTICIPATION_COUNT' AS metric, count(*)::text AS value FROM session_teacher_participations WHERE role='PRIMARY';
SELECT 'SUBSTITUTE_PARTICIPATION_COUNT' AS metric, count(*)::text AS value FROM session_teacher_participations WHERE role='SUBSTITUTE';
SELECT 'UNEXPECTED_TEACHER_ROLE_COUNT' AS metric, count(*)::text AS value FROM session_teacher_participations WHERE role IS NULL OR role NOT IN ('PRIMARY','SUBSTITUTE');
SELECT 'TEACHER_ATTENDANCE_TOTAL' AS metric, count(*)::text AS value FROM session_teacher_participations WHERE attendance_status IS NOT NULL;
SELECT 'TEACHER_PRESENT_WITH_NOTE_COUNT' AS metric, count(*)::text AS value FROM session_teacher_participations WHERE attendance_status='PRESENT' AND nullif(trim(coalesce(reason,'') || ' ' || coalesce(notes,'')),'') IS NOT NULL;
SELECT 'UNEXPECTED_TEACHER_ATTENDANCE_STATUS_COUNT' AS metric, count(*)::text AS value FROM session_teacher_participations WHERE attendance_status IS NOT NULL AND attendance_status NOT IN ('PRESENT','ABSENT','SICK','IZIN','OTHER');
SELECT 'TEACHER_ATTENDANCE_STATUS_DISTRIBUTION' AS metric, coalesce(string_agg(coalesce(attendance_status,'NULL') || '=' || n::text, ',' ORDER BY coalesce(attendance_status,'NULL')),'EMPTY') AS value FROM (SELECT attendance_status,count(*) n FROM session_teacher_participations GROUP BY attendance_status) s;

SELECT 'SESSIONS_WITH_SUBSTITUTE' AS metric, count(DISTINCT class_session_id)::text AS value FROM session_teacher_participations WHERE role='SUBSTITUTE';
SELECT 'MULTI_SUBSTITUTE_SESSION_COUNT' AS metric, count(*)::text AS value FROM (SELECT class_session_id FROM session_teacher_participations WHERE role='SUBSTITUTE' GROUP BY class_session_id HAVING count(*) > 1) s;
SELECT 'SUBSTITUTION_CHANGE_COUNT' AS metric, count(*)::text AS value FROM schedule_changes WHERE change_type='SUBSTITUTION';
SELECT 'SUBSTITUTION_CONFLICT_COUNT' AS metric, 'NOT_DERIVABLE' AS value;

SELECT 'SCHEDULE_CHANGE_TYPE_DISTRIBUTION' AS metric, coalesce(string_agg(coalesce(change_type,'NULL') || '=' || n::text, ',' ORDER BY coalesce(change_type,'NULL')),'EMPTY') AS value FROM (SELECT change_type,count(*) n FROM schedule_changes GROUP BY change_type) s;
SELECT 'SCHEDULE_CHANGE_STATUS_DISTRIBUTION' AS metric, coalesce(string_agg(coalesce(status,'NULL') || '=' || n::text, ',' ORDER BY coalesce(status,'NULL')),'EMPTY') AS value FROM (SELECT status,count(*) n FROM schedule_changes GROUP BY status) s;
SELECT 'SCHEDULE_CHANGE_MISSING_ACTOR_LINEAGE_COUNT' AS metric, count(*)::text AS value FROM schedule_changes WHERE requested_by_user_id IS NULL AND approved_by_user_id IS NULL AND applied_by_user_id IS NULL;
SELECT 'SCHEDULE_CHANGE_MISSING_REASON_COUNT' AS metric, count(*)::text AS value FROM schedule_changes WHERE nullif(trim(reason),'') IS NULL;

SELECT 'ACADEMIC_PERIOD_TOTAL' AS metric, count(*)::text AS value FROM (SELECT DISTINCT class_id, period_start, period_end FROM attendance_period_locks) p;
SELECT 'ACADEMIC_PERIOD_LOCKED_COUNT' AS metric, count(*)::text AS value FROM attendance_period_locks WHERE status='LOCKED';
SELECT 'ACADEMIC_PERIOD_OPEN_COUNT' AS metric, count(*)::text AS value FROM attendance_period_locks WHERE status='OPEN';
SELECT 'LOCKED_PERIODS_WITH_SESSIONS_COUNT' AS metric, count(*)::text AS value FROM attendance_period_locks l WHERE l.status='LOCKED' AND EXISTS (SELECT 1 FROM class_sessions cs WHERE cs.class_id=l.class_id AND cs.planned_start_at::date BETWEEN l.period_start AND l.period_end);

SELECT 'PUBLISHED_ACADEMIC_OUTPUT_COUNT' AS metric, ((SELECT count(*) FROM report_card_versions WHERE published_at IS NOT NULL) + (SELECT count(*) FROM academic_transcript_versions WHERE published_at IS NOT NULL))::text AS value;
SELECT 'PUBLISHED_PERIODS_AFFECTED_BY_OCCURRENCE_REBASE' AS metric, 'NOT_DERIVABLE' AS value;
SELECT 'MONTHLY_ATTENDANCE_SUMMARY_TOTAL' AS metric, count(*)::text AS value FROM monthly_attendance_summaries;
SELECT 'MONTHLY_STUDENT_SNAPSHOT_TOTAL' AS metric, count(*)::text AS value FROM monthly_student_attendance_snapshots;
SELECT 'IMPORT_BATCH_TOTAL' AS metric, count(*)::text AS value FROM import_batches;
SELECT 'IMPORT_LINEAGE_TOTAL' AS metric, count(*)::text AS value FROM import_lineages;
SELECT 'SOURCE_CERTIFICATION_TABLE_PRESENT' AS metric, CASE WHEN to_regclass('public.attendance_source_certifications') IS NULL THEN 'NO' ELSE 'YES' END AS value;
SELECT 'CLASS_LINEAGE_TABLE_PRESENT' AS metric, CASE WHEN to_regclass('public.class_lineage_mappings') IS NULL THEN 'NO' ELSE 'YES' END AS value;

SELECT 'LEGACY_SESSION_ROWS_REQUIRING_OCCURRENCE_STRATEGY' AS metric, count(*)::text AS value FROM class_sessions;
SELECT 'CANONICAL_SESSION_STATUS_DISTRIBUTION' AS metric, coalesce(string_agg(coalesce(session_status,'NULL') || '=' || n::text, ',' ORDER BY coalesce(session_status,'NULL')),'EMPTY') AS value FROM (SELECT session_status,count(*) n FROM class_sessions GROUP BY session_status) s;
