-- P1-R3: read-only PostgreSQL pre-staging audit.
-- Run this before migration 2026_09_11_000020.
-- Any value outside the expected set is an ABORT condition.

SELECT version() AS postgres_version, current_database() AS database_name, current_schema() AS schema_name;
SELECT to_regclass('public.migrations') AS migrations_table,
       to_regclass('public.roles') AS roles_table,
       to_regclass('public.user_role_assignments') AS assignments_table;

-- If migrations_table is non-NULL, inspect the migration state with the deployment tool;
-- do not mark the migration applied manually.

SELECT 'class_sessions.session_status' AS field, session_status AS value, COUNT(*) AS row_count
FROM class_sessions GROUP BY session_status ORDER BY value;
SELECT 'class_sessions.session_source' AS field, session_source AS value, COUNT(*) AS row_count
FROM class_sessions GROUP BY session_source ORDER BY value;
SELECT 'class_sessions.participant_scope' AS field, participant_scope AS value, COUNT(*) AS row_count
FROM class_sessions GROUP BY participant_scope ORDER BY value;

SELECT 'session_teacher_participations.role' AS field, role AS value, COUNT(*) AS row_count
FROM session_teacher_participations GROUP BY role ORDER BY value;
SELECT 'session_teacher_participations.obligation_type' AS field, obligation_type AS value, COUNT(*) AS row_count
FROM session_teacher_participations GROUP BY obligation_type ORDER BY value;
SELECT 'session_teacher_participations.participation_status' AS field, participation_status AS value, COUNT(*) AS row_count
FROM session_teacher_participations GROUP BY participation_status ORDER BY value;
SELECT 'session_teacher_participations.attendance_status' AS field, attendance_status AS value, COUNT(*) AS row_count
FROM session_teacher_participations GROUP BY attendance_status ORDER BY value NULLS FIRST;

SELECT 'student_attendance.attendance_status' AS field, attendance_status AS value, COUNT(*) AS row_count
FROM student_attendance GROUP BY attendance_status ORDER BY value NULLS FIRST;
SELECT 'student_attendance.workflow_status' AS field, workflow_status AS value, COUNT(*) AS row_count
FROM student_attendance GROUP BY workflow_status ORDER BY value;

SELECT r.id AS role_id, r.code AS role_code, ura.id AS assignment_id,
       ura.user_id, ura.scope_type, ura.scope_key, ura.effective_from, ura.effective_until
FROM roles r
LEFT JOIN user_role_assignments ura ON ura.role_id = r.id
WHERE r.code = 'ADMIN_AKADEMIK'
ORDER BY ura.effective_from NULLS FIRST, ura.id;

SELECT p.code AS permission_code, r.code AS role_code
FROM permissions p
LEFT JOIN role_permissions rp ON rp.permission_id = p.id
LEFT JOIN roles r ON r.id = rp.role_id
WHERE p.code IN ('academic.domain.manage', 'platform.institution.manage')
ORDER BY p.code, r.code;

SELECT c.conrelid::regclass AS table_name, c.conname AS constraint_name,
       pg_get_constraintdef(c.oid) AS definition
FROM pg_constraint c
WHERE c.conname IN (
    'chk_class_sessions_session_status',
    'chk_class_sessions_session_source',
    'chk_class_sessions_participant_scope',
    'chk_session_teacher_participations_role',
    'chk_session_teacher_participations_obligation_type',
    'chk_session_teacher_participations_participation_status',
    'chk_session_teacher_participations_attendance_status',
    'chk_student_attendance_attendance_status',
    'chk_student_attendance_workflow_status'
)
ORDER BY table_name, constraint_name;

-- Invalid-value diagnostics. Empty result sets are required before migration.
SELECT 'class_sessions.session_status' AS field, session_status AS invalid_value, COUNT(*) AS row_count
FROM class_sessions WHERE session_status NOT IN ('PLANNED','CONFIRMED','COMPLETED','CANCELLED','RESCHEDULED')
GROUP BY session_status;
SELECT 'class_sessions.session_source' AS field, session_source AS invalid_value, COUNT(*) AS row_count
FROM class_sessions WHERE session_source NOT IN ('SCHEDULED','EXTRA','RESCHEDULED','AD_HOC')
GROUP BY session_source;
SELECT 'class_sessions.participant_scope' AS field, participant_scope AS invalid_value, COUNT(*) AS row_count
FROM class_sessions WHERE participant_scope NOT IN ('FULL_CLASS','SELECTED_STUDENTS') GROUP BY participant_scope;
SELECT 'session_teacher_participations.role' AS field, role AS invalid_value, COUNT(*) AS row_count
FROM session_teacher_participations WHERE role NOT IN ('PRIMARY','SUBSTITUTE') GROUP BY role;
SELECT 'session_teacher_participations.obligation_type' AS field, obligation_type AS invalid_value, COUNT(*) AS row_count
FROM session_teacher_participations WHERE obligation_type NOT IN ('TEACHING_ASSIGNMENT','REPLACEMENT') GROUP BY obligation_type;
SELECT 'session_teacher_participations.participation_status' AS field, participation_status AS invalid_value, COUNT(*) AS row_count
FROM session_teacher_participations WHERE participation_status NOT IN ('EXPECTED') GROUP BY participation_status;
SELECT 'session_teacher_participations.attendance_status' AS field, attendance_status AS invalid_value, COUNT(*) AS row_count
FROM session_teacher_participations WHERE attendance_status IS NOT NULL AND attendance_status NOT IN ('PRESENT','ABSENT','SICK','IZIN','OTHER') GROUP BY attendance_status;
SELECT 'student_attendance.attendance_status' AS field, attendance_status AS invalid_value, COUNT(*) AS row_count
FROM student_attendance WHERE attendance_status IS NOT NULL AND attendance_status NOT IN ('PRESENT','ABSENT','SICK','IZIN','LATE','EXCUSED') GROUP BY attendance_status;
SELECT 'student_attendance.workflow_status' AS field, workflow_status AS invalid_value, COUNT(*) AS row_count
FROM student_attendance WHERE workflow_status NOT IN ('DRAFT','VALIDATED') GROUP BY workflow_status;

-- Expected non-NULL values:
-- session_status: PLANNED, CONFIRMED, COMPLETED, CANCELLED, RESCHEDULED
-- session_source: SCHEDULED, EXTRA, RESCHEDULED, AD_HOC
-- participant_scope: FULL_CLASS, SELECTED_STUDENTS
-- role: PRIMARY, SUBSTITUTE
-- obligation_type: TEACHING_ASSIGNMENT, REPLACEMENT
-- participation_status: EXPECTED
-- teacher attendance_status: PRESENT, ABSENT, SICK, IZIN, OTHER (NULL allowed)
-- student attendance_status: PRESENT, ABSENT, SICK, IZIN, LATE, EXCUSED (NULL allowed)
-- student workflow_status: DRAFT, VALIDATED
