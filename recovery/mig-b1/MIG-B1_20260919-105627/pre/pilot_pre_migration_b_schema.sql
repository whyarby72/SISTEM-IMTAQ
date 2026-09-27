--
-- PostgreSQL database dump
--

\restrict hgalMMskTUlwceQaf02l4i4L4242x7yvNJJKoS05Ro0uyTVELJjIL8tfY3wJBEI

-- Dumped from database version 18.6 (Homebrew)
-- Dumped by pg_dump version 18.6 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: btree_gist; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS btree_gist WITH SCHEMA public;


--
-- Name: EXTENSION btree_gist; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION btree_gist IS 'support for indexing common datatypes in GiST';


--
-- Name: prevent_audit_log_mutation(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.prevent_audit_log_mutation() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
                BEGIN
                    RAISE EXCEPTION 'audit_logs is append-only';
                END;
            $$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: academic_calendar_events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.academic_calendar_events (
    id uuid NOT NULL,
    academic_year_id uuid NOT NULL,
    event_type character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    start_at timestamp(0) with time zone NOT NULL,
    end_at timestamp(0) with time zone NOT NULL,
    organizational_unit_id uuid,
    class_id uuid,
    regular_session_policy character varying(255) NOT NULL,
    notes text,
    workflow_status character varying(255) NOT NULL,
    created_by_user_id bigint,
    updated_by_user_id bigint,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: academic_transcript_lines; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.academic_transcript_lines (
    id uuid NOT NULL,
    academic_transcript_version_id uuid CONSTRAINT academic_transcript_lines_academic_transcript_version__not_null NOT NULL,
    semester_id uuid NOT NULL,
    subject_id uuid NOT NULL,
    semester_name_snapshot character varying(255) NOT NULL,
    subject_name_snapshot character varying(255) NOT NULL,
    semester_subject_grade_id uuid NOT NULL,
    grade_version_no integer NOT NULL,
    score numeric(5,2) NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: academic_transcript_signatories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.academic_transcript_signatories (
    id uuid NOT NULL,
    academic_transcript_version_id uuid CONSTRAINT academic_transcript_signato_academic_transcript_versio_not_null NOT NULL,
    signatory_role character varying(255) NOT NULL,
    staff_id uuid,
    name_snapshot character varying(255) NOT NULL,
    title_snapshot character varying(255) NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: academic_transcript_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.academic_transcript_versions (
    id uuid NOT NULL,
    academic_transcript_id uuid NOT NULL,
    version_no integer NOT NULL,
    status character varying(255) DEFAULT 'DRAFT'::character varying NOT NULL,
    source_cutoff_at timestamp(0) with time zone NOT NULL,
    student_name_snapshot character varying(255) NOT NULL,
    created_by bigint NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    reviewed_by bigint,
    reviewed_at timestamp(0) with time zone,
    approved_by bigint,
    approved_at timestamp(0) with time zone,
    published_by bigint,
    published_at timestamp(0) with time zone
);


--
-- Name: academic_transcripts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.academic_transcripts (
    id uuid NOT NULL,
    student_id uuid NOT NULL,
    transcript_type character varying(255) DEFAULT 'ACADEMIC'::character varying NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: academic_years; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.academic_years (
    id uuid NOT NULL,
    year_code character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    starts_on date NOT NULL,
    ends_on date NOT NULL,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: alert_actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.alert_actions (
    id uuid NOT NULL,
    alert_id uuid NOT NULL,
    actor_user_id bigint NOT NULL,
    action_type character varying(255) NOT NULL,
    from_status character varying(255),
    to_status character varying(255),
    notes text,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: alert_rules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.alert_rules (
    id uuid NOT NULL,
    rule_code character varying(255) NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    supersedes_rule_id uuid,
    name character varying(255) NOT NULL,
    category character varying(255) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    configuration json,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: alerts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.alerts (
    id uuid NOT NULL,
    alert_rule_id uuid NOT NULL,
    fingerprint character varying(255) NOT NULL,
    dedup_key character varying(255),
    entity_type character varying(255),
    entity_id uuid,
    owner_user_id bigint,
    severity character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'OPEN'::character varying NOT NULL,
    due_at timestamp(0) with time zone,
    resolved_at timestamp(0) with time zone,
    resolved_by bigint,
    evidence json,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: attendance_period_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.attendance_period_locks (
    id uuid NOT NULL,
    class_id uuid NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    status character varying(255) DEFAULT 'OPEN'::character varying NOT NULL,
    locked_by bigint,
    locked_at timestamp(0) with time zone,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id uuid NOT NULL,
    occurred_at timestamp(0) with time zone NOT NULL,
    actor_user_id bigint,
    actor_type character varying(255) NOT NULL,
    action character varying(255) NOT NULL,
    entity_type character varying(255) NOT NULL,
    entity_id character varying(255) NOT NULL,
    version_before integer,
    version_after integer,
    old_values jsonb,
    new_values jsonb,
    reason text,
    source_channel character varying(255) NOT NULL,
    correlation_id character varying(255),
    request_id character varying(255),
    correction_request_id uuid,
    technical_metadata jsonb
);


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: class_homeroom_assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.class_homeroom_assignments (
    id uuid NOT NULL,
    class_id uuid NOT NULL,
    staff_id uuid NOT NULL,
    effective_from date NOT NULL,
    effective_until date,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    assigned_by_user_id bigint,
    assigned_at timestamp(0) with time zone,
    reason character varying(255),
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: class_session_groups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.class_session_groups (
    id uuid NOT NULL,
    class_session_id uuid NOT NULL,
    class_id uuid NOT NULL,
    scope_role character varying(255) DEFAULT 'TEACHING_SCOPE'::character varying NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: class_sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.class_sessions (
    id uuid NOT NULL,
    session_code character varying(255) NOT NULL,
    teaching_assignment_id uuid NOT NULL,
    schedule_rule_id uuid,
    class_id uuid NOT NULL,
    subject_id uuid NOT NULL,
    location_id uuid,
    planned_start_at timestamp(0) with time zone NOT NULL,
    planned_end_at timestamp(0) with time zone NOT NULL,
    actual_start_at timestamp(0) with time zone,
    actual_end_at timestamp(0) with time zone,
    session_source character varying(255) NOT NULL,
    participant_scope character varying(255) NOT NULL,
    session_status character varying(255) NOT NULL,
    rescheduled_from_session_id uuid,
    notes text,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    CONSTRAINT chk_class_sessions_participant_scope CHECK (((participant_scope)::text = ANY ((ARRAY['FULL_CLASS'::character varying, 'SELECTED_STUDENTS'::character varying])::text[]))),
    CONSTRAINT chk_class_sessions_session_source CHECK (((session_source)::text = ANY ((ARRAY['SCHEDULED'::character varying, 'EXTRA'::character varying, 'RESCHEDULED'::character varying, 'AD_HOC'::character varying])::text[]))),
    CONSTRAINT chk_class_sessions_session_status CHECK (((session_status)::text = ANY ((ARRAY['PLANNED'::character varying, 'CONFIRMED'::character varying, 'COMPLETED'::character varying, 'CANCELLED'::character varying, 'RESCHEDULED'::character varying])::text[])))
);


--
-- Name: classes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.classes (
    id uuid NOT NULL,
    class_code character varying(255) NOT NULL,
    academic_year_id uuid NOT NULL,
    organizational_unit_id uuid NOT NULL,
    grade_level_id uuid NOT NULL,
    section_code character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: correction_requests; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.correction_requests (
    id uuid NOT NULL,
    requested_by_user_id bigint NOT NULL,
    entity_type character varying(255) NOT NULL,
    entity_id character varying(255) NOT NULL,
    correction_type character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'PENDING'::character varying NOT NULL,
    requested_changes jsonb NOT NULL,
    reason text NOT NULL,
    reviewed_by_user_id bigint,
    reviewed_at timestamp(0) with time zone,
    applied_at timestamp(0) with time zone,
    rejection_reason text,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: grade_levels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.grade_levels (
    id uuid NOT NULL,
    organizational_unit_id uuid,
    level_code character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    sequence_no smallint NOT NULL,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: guardian_contact_channels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.guardian_contact_channels (
    id uuid NOT NULL,
    guardian_id uuid NOT NULL,
    channel_type character varying(255) NOT NULL,
    normalized_value character varying(255) NOT NULL,
    display_value character varying(255),
    verification_status character varying(255) DEFAULT 'UNVERIFIED'::character varying NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    active_from date,
    active_until date,
    verified_by_user_id bigint,
    verified_at timestamp(0) with time zone,
    source_reference character varying(255),
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: guardians; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.guardians (
    id uuid NOT NULL,
    guardian_code character varying(255) NOT NULL,
    full_name character varying(255) NOT NULL,
    record_status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: import_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_batches (
    id uuid NOT NULL,
    batch_code character varying(255) NOT NULL,
    source_system character varying(255) NOT NULL,
    source_period character varying(255),
    status character varying(255) DEFAULT 'DRAFT'::character varying NOT NULL,
    created_by_user_id bigint,
    received_at timestamp(0) with time zone,
    source_total integer DEFAULT 0 NOT NULL,
    imported_count integer DEFAULT 0 NOT NULL,
    rejected_count integer DEFAULT 0 NOT NULL,
    quarantined_count integer DEFAULT 0 NOT NULL,
    duplicate_count integer DEFAULT 0 NOT NULL,
    excluded_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: import_files; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_files (
    id uuid NOT NULL,
    import_batch_id uuid NOT NULL,
    original_filename character varying(255) NOT NULL,
    storage_path character varying(255) NOT NULL,
    sha256_checksum character varying(64) NOT NULL,
    source_granularity character varying(255) DEFAULT 'UNKNOWN'::character varying NOT NULL,
    file_size_bytes bigint,
    received_at timestamp(0) with time zone,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: import_lineages; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_lineages (
    id uuid NOT NULL,
    import_batch_id uuid NOT NULL,
    import_file_id uuid NOT NULL,
    import_row_id uuid NOT NULL,
    target_type character varying(255) NOT NULL,
    target_id character varying(255) NOT NULL,
    relationship_type character varying(255) DEFAULT 'IMPORTED_FROM'::character varying NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: import_mappings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_mappings (
    id uuid NOT NULL,
    import_batch_id uuid NOT NULL,
    source_type character varying(255) NOT NULL,
    source_key character varying(255) NOT NULL,
    target_type character varying(255),
    target_id character varying(255),
    mapping_status character varying(255) DEFAULT 'PENDING'::character varying NOT NULL,
    confidence numeric(5,4),
    reviewed_by_user_id bigint,
    reviewed_at timestamp(0) with time zone,
    evidence jsonb,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: import_row_errors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_row_errors (
    id uuid NOT NULL,
    import_row_id uuid NOT NULL,
    severity character varying(255) DEFAULT 'BLOCKING'::character varying NOT NULL,
    error_code character varying(255) NOT NULL,
    field_name character varying(255),
    message text NOT NULL,
    details jsonb,
    resolved_by_user_id bigint,
    resolved_at timestamp(0) with time zone,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: import_rows; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_rows (
    id uuid NOT NULL,
    import_batch_id uuid NOT NULL,
    import_file_id uuid NOT NULL,
    row_number integer NOT NULL,
    source_key character varying(255),
    raw_payload jsonb NOT NULL,
    row_status character varying(255) DEFAULT 'PENDING'::character varying NOT NULL,
    canonical_entity_type character varying(255),
    canonical_entity_id character varying(255),
    accounted_at timestamp(0) with time zone,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: locations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.locations (
    id uuid NOT NULL,
    location_code character varying(255) NOT NULL,
    location_name character varying(255) NOT NULL,
    location_type character varying(255) NOT NULL,
    organizational_unit_id uuid,
    address_text text,
    effective_from date,
    effective_until date,
    record_status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: monthly_attendance_summaries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.monthly_attendance_summaries (
    id uuid NOT NULL,
    period character varying(7) NOT NULL,
    class_id uuid NOT NULL,
    attendance_group character varying(255) NOT NULL,
    matiq_report_class character varying(255) NOT NULL,
    roster integer NOT NULL,
    present integer NOT NULL,
    permission integer NOT NULL,
    sick integer NOT NULL,
    absent integer NOT NULL,
    eligible integer NOT NULL,
    non_eligible integer NOT NULL,
    attendance_rate numeric(8,6) NOT NULL,
    non_eligible_reason character varying(255),
    import_batch_id uuid NOT NULL,
    source_checksum character varying(64) NOT NULL,
    status character varying(255) DEFAULT 'DRAFT'::character varying NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    published_by_user_id bigint,
    published_at timestamp(0) with time zone
);


--
-- Name: monthly_student_attendance_snapshots; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.monthly_student_attendance_snapshots (
    id uuid NOT NULL,
    period character varying(7) NOT NULL,
    student_id uuid NOT NULL,
    class_id uuid NOT NULL,
    class_admin character varying(255) NOT NULL,
    attendance_group character varying(255) NOT NULL,
    matiq_report_class character varying(255) CONSTRAINT monthly_student_attendance_snapshot_matiq_report_class_not_null NOT NULL,
    source_record_id character varying(255) NOT NULL,
    source_checksum character varying(64) NOT NULL,
    import_batch_id uuid NOT NULL,
    import_file_id uuid NOT NULL,
    import_row_id uuid NOT NULL,
    scheduled_attendance_units_working integer CONSTRAINT monthly_student_attendance__scheduled_attendance_units_not_null NOT NULL,
    present integer NOT NULL,
    permission integer NOT NULL,
    sick integer NOT NULL,
    absent integer NOT NULL,
    eligible integer NOT NULL,
    non_eligible integer NOT NULL,
    non_eligible_reason character varying(255),
    attendance_rate numeric(8,6) NOT NULL,
    source_absent_term character varying(255),
    raw_payload jsonb,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    CONSTRAINT monthly_student_snapshots_formula_check CHECK ((eligible = (((present + permission) + sick) + absent))),
    CONSTRAINT monthly_student_snapshots_rate_check CHECK (((attendance_rate >= (0)::numeric) AND (attendance_rate <= (1)::numeric))),
    CONSTRAINT monthly_student_snapshots_scheduled_check CHECK ((scheduled_attendance_units_working >= (eligible + non_eligible)))
);


--
-- Name: organizational_units; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organizational_units (
    id uuid NOT NULL,
    unit_code character varying(255) NOT NULL,
    unit_name character varying(255) NOT NULL,
    unit_type character varying(255) NOT NULL,
    parent_unit_id uuid,
    effective_from date,
    effective_until date,
    record_status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    id uuid NOT NULL,
    code character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: report_card_attendance_lines; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.report_card_attendance_lines (
    id uuid NOT NULL,
    report_card_version_id uuid NOT NULL,
    status_code character varying(255) NOT NULL,
    status_count integer NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: report_card_notes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.report_card_notes (
    id uuid NOT NULL,
    report_card_version_id uuid NOT NULL,
    note_text text NOT NULL,
    approved_for_parent_report boolean DEFAULT false NOT NULL,
    created_by bigint NOT NULL,
    updated_by bigint NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: report_card_signatories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.report_card_signatories (
    id uuid NOT NULL,
    report_card_version_id uuid NOT NULL,
    signatory_role character varying(255) NOT NULL,
    staff_id uuid,
    name_snapshot character varying(255) NOT NULL,
    title_snapshot character varying(255) NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: report_card_subject_lines; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.report_card_subject_lines (
    id uuid NOT NULL,
    report_card_version_id uuid NOT NULL,
    subject_id uuid NOT NULL,
    subject_name_snapshot character varying(255) NOT NULL,
    semester_subject_grade_id uuid NOT NULL,
    grade_version_no integer NOT NULL,
    score numeric(5,2) NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: report_card_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.report_card_versions (
    id uuid NOT NULL,
    report_card_id uuid NOT NULL,
    version_no integer NOT NULL,
    status character varying(255) DEFAULT 'DRAFT'::character varying NOT NULL,
    source_cutoff_at timestamp(0) with time zone NOT NULL,
    student_name_snapshot character varying(255) NOT NULL,
    class_id uuid NOT NULL,
    class_name_snapshot character varying(255) NOT NULL,
    semester_name_snapshot character varying(255) NOT NULL,
    created_by bigint NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    reviewed_by bigint,
    reviewed_at timestamp(0) with time zone,
    approved_by bigint,
    approved_at timestamp(0) with time zone,
    published_by bigint,
    published_at timestamp(0) with time zone
);


--
-- Name: report_cards; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.report_cards (
    id uuid NOT NULL,
    student_id uuid NOT NULL,
    semester_id uuid NOT NULL,
    report_type character varying(255) DEFAULT 'SEMESTER'::character varying NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_permissions (
    role_id uuid NOT NULL,
    permission_id uuid NOT NULL
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id uuid NOT NULL,
    code character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    is_system boolean DEFAULT false NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: schedule_changes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedule_changes (
    id uuid NOT NULL,
    change_code character varying(255) NOT NULL,
    change_type character varying(255) NOT NULL,
    source_session_id uuid,
    related_session_id uuid,
    original_teacher_id uuid,
    replacement_teacher_id uuid,
    new_start_at timestamp(0) with time zone,
    new_end_at timestamp(0) with time zone,
    reason text NOT NULL,
    requested_by_user_id bigint,
    requested_at timestamp(0) with time zone,
    approved_by_user_id bigint,
    approved_at timestamp(0) with time zone,
    applied_by_user_id bigint,
    applied_at timestamp(0) with time zone,
    status character varying(255) NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: schedule_rule_groups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedule_rule_groups (
    id uuid NOT NULL,
    schedule_rule_id uuid NOT NULL,
    class_id uuid NOT NULL,
    scope_role character varying(255) DEFAULT 'TEACHING_SCOPE'::character varying NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: schedule_rule_week_numbers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedule_rule_week_numbers (
    schedule_rule_id uuid NOT NULL,
    week_no smallint NOT NULL
);


--
-- Name: schedule_rules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedule_rules (
    id uuid NOT NULL,
    teaching_assignment_id uuid NOT NULL,
    weekday smallint NOT NULL,
    start_time time(0) without time zone NOT NULL,
    end_time time(0) without time zone NOT NULL,
    recurrence_type character varying(255) NOT NULL,
    location_id uuid,
    effective_from date NOT NULL,
    effective_until date,
    workflow_status character varying(255) NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: semester_subject_grades; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.semester_subject_grades (
    id uuid NOT NULL,
    student_id uuid NOT NULL,
    semester_id uuid NOT NULL,
    subject_id uuid NOT NULL,
    score numeric(5,2),
    grade_source character varying(255) DEFAULT 'DIRECT_ENTRY'::character varying NOT NULL,
    source_teaching_assignment_id uuid,
    responsible_staff_id uuid,
    workflow_status character varying(255) DEFAULT 'DRAFT'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    entered_by bigint NOT NULL,
    entered_at timestamp(0) with time zone NOT NULL,
    finalized_by bigint,
    finalized_at timestamp(0) with time zone,
    updated_by bigint NOT NULL,
    updated_at timestamp(0) with time zone NOT NULL,
    created_at timestamp(0) with time zone,
    CONSTRAINT semester_subject_grades_score_check CHECK (((score IS NULL) OR ((score >= (0)::numeric) AND (score <= (100)::numeric)))),
    CONSTRAINT semester_subject_grades_source_check CHECK (((grade_source)::text = ANY ((ARRAY['DIRECT_ENTRY'::character varying, 'IMPORTED'::character varying])::text[])))
);


--
-- Name: semesters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.semesters (
    id uuid NOT NULL,
    academic_year_id uuid NOT NULL,
    semester_code character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    sequence_no smallint NOT NULL,
    starts_on date NOT NULL,
    ends_on date NOT NULL,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: session_student_participants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.session_student_participants (
    id uuid NOT NULL,
    class_session_id uuid NOT NULL,
    student_id uuid NOT NULL,
    participant_basis character varying(255) NOT NULL,
    participant_status character varying(255) DEFAULT 'EXPECTED'::character varying NOT NULL,
    is_required boolean DEFAULT true NOT NULL,
    removal_reason character varying(255),
    removed_by_user_id bigint,
    removed_at timestamp(0) with time zone,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: session_teacher_participations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.session_teacher_participations (
    id uuid NOT NULL,
    class_session_id uuid NOT NULL,
    teacher_staff_id uuid NOT NULL,
    role character varying(255) NOT NULL,
    obligation_type character varying(255) NOT NULL,
    participation_status character varying(255) DEFAULT 'EXPECTED'::character varying NOT NULL,
    attendance_status character varying(255),
    reason character varying(255),
    schedule_change_id uuid,
    checkin_at timestamp(0) with time zone,
    checkout_at timestamp(0) with time zone,
    notes text,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    CONSTRAINT chk_session_teacher_participations_attendance_status CHECK (((attendance_status IS NULL) OR ((attendance_status)::text = ANY ((ARRAY['PRESENT'::character varying, 'ABSENT'::character varying, 'SICK'::character varying, 'IZIN'::character varying, 'OTHER'::character varying])::text[])))),
    CONSTRAINT chk_session_teacher_participations_obligation_type CHECK (((obligation_type)::text = ANY ((ARRAY['TEACHING_ASSIGNMENT'::character varying, 'REPLACEMENT'::character varying])::text[]))),
    CONSTRAINT chk_session_teacher_participations_participation_status CHECK (((participation_status)::text = 'EXPECTED'::text)),
    CONSTRAINT chk_session_teacher_participations_role CHECK (((role)::text = ANY ((ARRAY['PRIMARY'::character varying, 'SUBSTITUTE'::character varying])::text[])))
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: staff; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.staff (
    id uuid NOT NULL,
    staff_code character varying(255) NOT NULL,
    full_name character varying(255) NOT NULL,
    record_status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    active_from date,
    active_until date,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: staff_organizational_assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.staff_organizational_assignments (
    id uuid NOT NULL,
    staff_id uuid NOT NULL,
    organizational_unit_id uuid CONSTRAINT staff_organizational_assignment_organizational_unit_id_not_null NOT NULL,
    assignment_type character varying(255) NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    effective_from date NOT NULL,
    effective_until date,
    source_reference character varying(255),
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: staging_imtaq_attendance_july_2026; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.staging_imtaq_attendance_july_2026 (
    source_record_id text NOT NULL,
    period character(7) NOT NULL,
    student_id text,
    class_admin text NOT NULL,
    attendance_group text NOT NULL,
    scheduled_attendance_units_working integer CONSTRAINT staging_imtaq_attendance_ju_scheduled_attendance_units_not_null NOT NULL,
    present integer NOT NULL,
    permission integer NOT NULL,
    sick integer NOT NULL,
    absent integer NOT NULL,
    eligible integer NOT NULL,
    non_eligible integer NOT NULL,
    non_eligible_reason text,
    attendance_rate numeric(10,8),
    source_absent_term text,
    data_status text NOT NULL,
    validation_status text NOT NULL,
    CONSTRAINT chk_jul26_eligible CHECK ((eligible = (((present + permission) + sick) + absent)))
);


--
-- Name: staging_imtaq_students_july_2026; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.staging_imtaq_students_july_2026 (
    source_record_id text NOT NULL,
    student_id text,
    name_indonesia text NOT NULL,
    name_arabic text,
    class_admin text NOT NULL,
    level smallint NOT NULL,
    track text,
    attendance_group text NOT NULL,
    matiq_report_class text NOT NULL,
    active_for_period boolean DEFAULT true NOT NULL,
    needs_student_id_mapping boolean DEFAULT true CONSTRAINT staging_imtaq_students_july_2_needs_student_id_mapping_not_null NOT NULL,
    data_status text NOT NULL
);


--
-- Name: student_attendance; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.student_attendance (
    id uuid NOT NULL,
    session_student_participant_id uuid NOT NULL,
    attendance_status character varying(255),
    reason_code character varying(255),
    permission_event_id uuid,
    arrival_at timestamp(0) with time zone,
    departure_at timestamp(0) with time zone,
    notes text,
    workflow_status character varying(255) DEFAULT 'DRAFT'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    entered_by bigint NOT NULL,
    entered_at timestamp(0) with time zone NOT NULL,
    finalized_by bigint,
    finalized_at timestamp(0) with time zone,
    updated_by bigint NOT NULL,
    updated_at timestamp(0) with time zone NOT NULL,
    CONSTRAINT chk_student_attendance_attendance_status CHECK (((attendance_status IS NULL) OR ((attendance_status)::text = ANY ((ARRAY['PRESENT'::character varying, 'ABSENT'::character varying, 'SICK'::character varying, 'IZIN'::character varying, 'LATE'::character varying, 'EXCUSED'::character varying])::text[])))),
    CONSTRAINT chk_student_attendance_workflow_status CHECK (((workflow_status)::text = ANY ((ARRAY['DRAFT'::character varying, 'VALIDATED'::character varying])::text[])))
);


--
-- Name: student_class_enrollments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.student_class_enrollments (
    id uuid NOT NULL,
    student_id uuid NOT NULL,
    class_id uuid NOT NULL,
    effective_from date NOT NULL,
    effective_until date,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    reason character varying(255),
    source_reference character varying(255),
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: student_guardian_relationships; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.student_guardian_relationships (
    id uuid NOT NULL,
    student_id uuid NOT NULL,
    guardian_id uuid NOT NULL,
    relationship_type character varying(255) NOT NULL,
    effective_from date,
    effective_until date,
    relationship_status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    communication_priority smallint,
    authorized_for_parent_reports boolean DEFAULT false CONSTRAINT student_guardian_relationsh_authorized_for_parent_repo_not_null NOT NULL,
    source_reference character varying(255),
    notes text,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: student_identifiers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.student_identifiers (
    id uuid NOT NULL,
    student_id uuid NOT NULL,
    identifier_type character varying(255) NOT NULL,
    identifier_value character varying(255) NOT NULL,
    identifier_scope character varying(255),
    verification_status character varying(255) DEFAULT 'UNVERIFIED'::character varying NOT NULL,
    record_status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    valid_from date,
    valid_until date,
    source_reference character varying(255),
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: student_session_grooming_notes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.student_session_grooming_notes (
    id uuid NOT NULL,
    session_student_participant_id uuid CONSTRAINT student_session_grooming_no_session_student_participan_not_null NOT NULL,
    note_text text,
    created_by bigint NOT NULL,
    created_at timestamp(0) with time zone NOT NULL,
    updated_by bigint NOT NULL,
    updated_at timestamp(0) with time zone NOT NULL,
    discipline_code character varying(255)
);


--
-- Name: student_status_history; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.student_status_history (
    id uuid NOT NULL,
    student_id uuid NOT NULL,
    status character varying(255) NOT NULL,
    effective_from date NOT NULL,
    effective_until date,
    decision_reference character varying(255),
    reason text,
    actor_user_id bigint,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: students; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.students (
    id uuid NOT NULL,
    student_code character varying(255),
    full_name character varying(255) NOT NULL,
    arabic_name character varying(255),
    nickname character varying(255),
    gender_code character varying(255),
    birth_place character varying(255),
    birth_date date,
    entry_year smallint,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    nis character varying(255),
    nisn character varying(255)
);


--
-- Name: subjects; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.subjects (
    id uuid NOT NULL,
    subject_code character varying(255) NOT NULL,
    subject_name character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: teaching_assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.teaching_assignments (
    id uuid NOT NULL,
    assignment_code character varying(255) NOT NULL,
    semester_id uuid NOT NULL,
    class_id uuid NOT NULL,
    subject_id uuid NOT NULL,
    teacher_staff_id uuid NOT NULL,
    effective_from date NOT NULL,
    effective_until date,
    workflow_status character varying(255) NOT NULL,
    source_reference character varying(255),
    created_by_user_id bigint,
    updated_by_user_id bigint,
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: user_role_assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_role_assignments (
    id uuid NOT NULL,
    user_id bigint NOT NULL,
    role_id uuid NOT NULL,
    scope_type character varying(255) DEFAULT 'INSTITUTION'::character varying NOT NULL,
    scope_key character varying(255),
    effective_from date,
    effective_until date,
    assignment_reason character varying(255),
    version_no integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: user_staff_links; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_staff_links (
    id uuid NOT NULL,
    user_id bigint NOT NULL,
    staff_id uuid NOT NULL,
    effective_from date,
    effective_until date,
    linked_by_user_id bigint,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: academic_calendar_events academic_calendar_events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_calendar_events
    ADD CONSTRAINT academic_calendar_events_pkey PRIMARY KEY (id);


--
-- Name: academic_transcript_lines academic_transcript_lines_academic_transcript_version_id_semest; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_lines
    ADD CONSTRAINT academic_transcript_lines_academic_transcript_version_id_semest UNIQUE (academic_transcript_version_id, semester_id, subject_id);


--
-- Name: academic_transcript_lines academic_transcript_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_lines
    ADD CONSTRAINT academic_transcript_lines_pkey PRIMARY KEY (id);


--
-- Name: academic_transcript_signatories academic_transcript_signatories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_signatories
    ADD CONSTRAINT academic_transcript_signatories_pkey PRIMARY KEY (id);


--
-- Name: academic_transcript_versions academic_transcript_versions_academic_transcript_id_version_no_; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_versions
    ADD CONSTRAINT academic_transcript_versions_academic_transcript_id_version_no_ UNIQUE (academic_transcript_id, version_no);


--
-- Name: academic_transcript_versions academic_transcript_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_versions
    ADD CONSTRAINT academic_transcript_versions_pkey PRIMARY KEY (id);


--
-- Name: academic_transcripts academic_transcripts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcripts
    ADD CONSTRAINT academic_transcripts_pkey PRIMARY KEY (id);


--
-- Name: academic_transcripts academic_transcripts_student_id_transcript_type_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcripts
    ADD CONSTRAINT academic_transcripts_student_id_transcript_type_unique UNIQUE (student_id, transcript_type);


--
-- Name: academic_years academic_years_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_years
    ADD CONSTRAINT academic_years_pkey PRIMARY KEY (id);


--
-- Name: academic_years academic_years_year_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_years
    ADD CONSTRAINT academic_years_year_code_unique UNIQUE (year_code);


--
-- Name: alert_actions alert_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alert_actions
    ADD CONSTRAINT alert_actions_pkey PRIMARY KEY (id);


--
-- Name: alert_rules alert_rules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alert_rules
    ADD CONSTRAINT alert_rules_pkey PRIMARY KEY (id);


--
-- Name: alert_rules alert_rules_rule_code_version_no_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alert_rules
    ADD CONSTRAINT alert_rules_rule_code_version_no_unique UNIQUE (rule_code, version_no);


--
-- Name: alerts alerts_alert_rule_id_dedup_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alerts
    ADD CONSTRAINT alerts_alert_rule_id_dedup_key_unique UNIQUE (alert_rule_id, dedup_key);


--
-- Name: alerts alerts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alerts
    ADD CONSTRAINT alerts_pkey PRIMARY KEY (id);


--
-- Name: attendance_period_locks attendance_period_locks_class_id_period_start_period_end_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_period_locks
    ADD CONSTRAINT attendance_period_locks_class_id_period_start_period_end_unique UNIQUE (class_id, period_start, period_end);


--
-- Name: attendance_period_locks attendance_period_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_period_locks
    ADD CONSTRAINT attendance_period_locks_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: class_homeroom_assignments class_homeroom_assignments_no_overlap; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_homeroom_assignments
    ADD CONSTRAINT class_homeroom_assignments_no_overlap EXCLUDE USING gist (class_id WITH =, daterange(effective_from, effective_until, '[)'::text) WITH &&);


--
-- Name: class_homeroom_assignments class_homeroom_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_homeroom_assignments
    ADD CONSTRAINT class_homeroom_assignments_pkey PRIMARY KEY (id);


--
-- Name: class_session_groups class_session_groups_class_session_id_class_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_session_groups
    ADD CONSTRAINT class_session_groups_class_session_id_class_id_unique UNIQUE (class_session_id, class_id);


--
-- Name: class_session_groups class_session_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_session_groups
    ADD CONSTRAINT class_session_groups_pkey PRIMARY KEY (id);


--
-- Name: class_sessions class_sessions_active_no_overlap; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_active_no_overlap EXCLUDE USING gist (class_id WITH =, tstzrange(planned_start_at, planned_end_at, '[)'::text) WITH &&) WHERE (((session_status)::text = ANY ((ARRAY['PLANNED'::character varying, 'CONFIRMED'::character varying, 'COMPLETED'::character varying])::text[])));


--
-- Name: class_sessions class_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_pkey PRIMARY KEY (id);


--
-- Name: class_sessions class_sessions_session_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_session_code_unique UNIQUE (session_code);


--
-- Name: classes classes_class_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_class_code_unique UNIQUE (class_code);


--
-- Name: classes classes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_pkey PRIMARY KEY (id);


--
-- Name: classes classes_year_unit_grade_section_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_year_unit_grade_section_unique UNIQUE (academic_year_id, organizational_unit_id, grade_level_id, section_code);


--
-- Name: correction_requests correction_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.correction_requests
    ADD CONSTRAINT correction_requests_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: grade_levels grade_levels_organizational_unit_id_level_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.grade_levels
    ADD CONSTRAINT grade_levels_organizational_unit_id_level_code_unique UNIQUE (organizational_unit_id, level_code);


--
-- Name: grade_levels grade_levels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.grade_levels
    ADD CONSTRAINT grade_levels_pkey PRIMARY KEY (id);


--
-- Name: guardian_contact_channels guardian_contact_channels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guardian_contact_channels
    ADD CONSTRAINT guardian_contact_channels_pkey PRIMARY KEY (id);


--
-- Name: guardians guardians_guardian_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guardians
    ADD CONSTRAINT guardians_guardian_code_unique UNIQUE (guardian_code);


--
-- Name: guardians guardians_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guardians
    ADD CONSTRAINT guardians_pkey PRIMARY KEY (id);


--
-- Name: import_batches import_batches_batch_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_batches
    ADD CONSTRAINT import_batches_batch_code_unique UNIQUE (batch_code);


--
-- Name: import_batches import_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_batches
    ADD CONSTRAINT import_batches_pkey PRIMARY KEY (id);


--
-- Name: import_files import_files_id_import_batch_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_files
    ADD CONSTRAINT import_files_id_import_batch_id_unique UNIQUE (id, import_batch_id);


--
-- Name: import_files import_files_import_batch_id_sha256_checksum_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_files
    ADD CONSTRAINT import_files_import_batch_id_sha256_checksum_unique UNIQUE (import_batch_id, sha256_checksum);


--
-- Name: import_files import_files_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_files
    ADD CONSTRAINT import_files_pkey PRIMARY KEY (id);


--
-- Name: import_lineages import_lineages_import_row_id_target_type_target_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_lineages
    ADD CONSTRAINT import_lineages_import_row_id_target_type_target_id_unique UNIQUE (import_row_id, target_type, target_id);


--
-- Name: import_lineages import_lineages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_lineages
    ADD CONSTRAINT import_lineages_pkey PRIMARY KEY (id);


--
-- Name: import_mappings import_mappings_import_batch_id_source_type_source_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_mappings
    ADD CONSTRAINT import_mappings_import_batch_id_source_type_source_key_unique UNIQUE (import_batch_id, source_type, source_key);


--
-- Name: import_mappings import_mappings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_mappings
    ADD CONSTRAINT import_mappings_pkey PRIMARY KEY (id);


--
-- Name: import_row_errors import_row_errors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_row_errors
    ADD CONSTRAINT import_row_errors_pkey PRIMARY KEY (id);


--
-- Name: import_rows import_rows_id_import_batch_id_import_file_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_rows
    ADD CONSTRAINT import_rows_id_import_batch_id_import_file_id_unique UNIQUE (id, import_batch_id, import_file_id);


--
-- Name: import_rows import_rows_import_file_id_row_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_rows
    ADD CONSTRAINT import_rows_import_file_id_row_number_unique UNIQUE (import_file_id, row_number);


--
-- Name: import_rows import_rows_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_rows
    ADD CONSTRAINT import_rows_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: locations locations_location_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_location_code_unique UNIQUE (location_code);


--
-- Name: locations locations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: monthly_attendance_summaries monthly_attendance_summaries_period_class_id_source_checksum_un; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_attendance_summaries
    ADD CONSTRAINT monthly_attendance_summaries_period_class_id_source_checksum_un UNIQUE (period, class_id, source_checksum);


--
-- Name: monthly_attendance_summaries monthly_attendance_summaries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_attendance_summaries
    ADD CONSTRAINT monthly_attendance_summaries_pkey PRIMARY KEY (id);


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_import_row_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_import_row_id_unique UNIQUE (import_row_id);


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_period_student_id_source_c; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_period_student_id_source_c UNIQUE (period, student_id, source_checksum);


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_pkey PRIMARY KEY (id);


--
-- Name: organizational_units organizational_units_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizational_units
    ADD CONSTRAINT organizational_units_pkey PRIMARY KEY (id);


--
-- Name: organizational_units organizational_units_unit_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizational_units
    ADD CONSTRAINT organizational_units_unit_code_unique UNIQUE (unit_code);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permissions permissions_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_code_unique UNIQUE (code);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: report_card_attendance_lines report_card_attendance_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_attendance_lines
    ADD CONSTRAINT report_card_attendance_lines_pkey PRIMARY KEY (id);


--
-- Name: report_card_attendance_lines report_card_attendance_lines_report_card_version_id_status_code; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_attendance_lines
    ADD CONSTRAINT report_card_attendance_lines_report_card_version_id_status_code UNIQUE (report_card_version_id, status_code);


--
-- Name: report_card_notes report_card_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_notes
    ADD CONSTRAINT report_card_notes_pkey PRIMARY KEY (id);


--
-- Name: report_card_notes report_card_notes_report_card_version_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_notes
    ADD CONSTRAINT report_card_notes_report_card_version_id_unique UNIQUE (report_card_version_id);


--
-- Name: report_card_signatories report_card_signatories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_signatories
    ADD CONSTRAINT report_card_signatories_pkey PRIMARY KEY (id);


--
-- Name: report_card_signatories report_card_signatories_report_card_version_id_signatory_role_u; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_signatories
    ADD CONSTRAINT report_card_signatories_report_card_version_id_signatory_role_u UNIQUE (report_card_version_id, signatory_role);


--
-- Name: report_card_subject_lines report_card_subject_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_subject_lines
    ADD CONSTRAINT report_card_subject_lines_pkey PRIMARY KEY (id);


--
-- Name: report_card_subject_lines report_card_subject_lines_report_card_version_id_subject_id_uni; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_subject_lines
    ADD CONSTRAINT report_card_subject_lines_report_card_version_id_subject_id_uni UNIQUE (report_card_version_id, subject_id);


--
-- Name: report_card_versions report_card_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_pkey PRIMARY KEY (id);


--
-- Name: report_card_versions report_card_versions_report_card_id_version_no_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_report_card_id_version_no_unique UNIQUE (report_card_id, version_no);


--
-- Name: report_cards report_cards_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_cards
    ADD CONSTRAINT report_cards_pkey PRIMARY KEY (id);


--
-- Name: report_cards report_cards_student_id_semester_id_report_type_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_cards
    ADD CONSTRAINT report_cards_student_id_semester_id_report_type_unique UNIQUE (student_id, semester_id, report_type);


--
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (role_id, permission_id);


--
-- Name: roles roles_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_code_unique UNIQUE (code);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: schedule_changes schedule_changes_change_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_change_code_unique UNIQUE (change_code);


--
-- Name: schedule_changes schedule_changes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_pkey PRIMARY KEY (id);


--
-- Name: schedule_rule_groups schedule_rule_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rule_groups
    ADD CONSTRAINT schedule_rule_groups_pkey PRIMARY KEY (id);


--
-- Name: schedule_rule_groups schedule_rule_groups_schedule_rule_id_class_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rule_groups
    ADD CONSTRAINT schedule_rule_groups_schedule_rule_id_class_id_unique UNIQUE (schedule_rule_id, class_id);


--
-- Name: schedule_rule_week_numbers schedule_rule_week_numbers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rule_week_numbers
    ADD CONSTRAINT schedule_rule_week_numbers_pkey PRIMARY KEY (schedule_rule_id, week_no);


--
-- Name: schedule_rules schedule_rules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rules
    ADD CONSTRAINT schedule_rules_pkey PRIMARY KEY (id);


--
-- Name: semester_subject_grades semester_subject_grades_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_pkey PRIMARY KEY (id);


--
-- Name: semester_subject_grades semester_subject_grades_student_id_semester_id_subject_id_uniqu; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_student_id_semester_id_subject_id_uniqu UNIQUE (student_id, semester_id, subject_id);


--
-- Name: semesters semesters_academic_year_id_semester_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semesters
    ADD CONSTRAINT semesters_academic_year_id_semester_code_unique UNIQUE (academic_year_id, semester_code);


--
-- Name: semesters semesters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semesters
    ADD CONSTRAINT semesters_pkey PRIMARY KEY (id);


--
-- Name: session_student_participants session_student_participants_class_session_id_student_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_student_participants
    ADD CONSTRAINT session_student_participants_class_session_id_student_id_unique UNIQUE (class_session_id, student_id);


--
-- Name: session_student_participants session_student_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_student_participants
    ADD CONSTRAINT session_student_participants_pkey PRIMARY KEY (id);


--
-- Name: session_teacher_participations session_teacher_participations_class_session_id_teacher_staff_i; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_teacher_participations
    ADD CONSTRAINT session_teacher_participations_class_session_id_teacher_staff_i UNIQUE (class_session_id, teacher_staff_id);


--
-- Name: session_teacher_participations session_teacher_participations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_teacher_participations
    ADD CONSTRAINT session_teacher_participations_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: staff_organizational_assignments staff_organizational_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.staff_organizational_assignments
    ADD CONSTRAINT staff_organizational_assignments_pkey PRIMARY KEY (id);


--
-- Name: staff staff_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_pkey PRIMARY KEY (id);


--
-- Name: staff staff_staff_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_staff_code_unique UNIQUE (staff_code);


--
-- Name: staging_imtaq_attendance_july_2026 staging_imtaq_attendance_july_2026_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.staging_imtaq_attendance_july_2026
    ADD CONSTRAINT staging_imtaq_attendance_july_2026_pkey PRIMARY KEY (source_record_id);


--
-- Name: staging_imtaq_students_july_2026 staging_imtaq_students_july_2026_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.staging_imtaq_students_july_2026
    ADD CONSTRAINT staging_imtaq_students_july_2026_pkey PRIMARY KEY (source_record_id);


--
-- Name: student_attendance student_attendance_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_pkey PRIMARY KEY (id);


--
-- Name: student_attendance student_attendance_session_student_participant_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_session_student_participant_id_unique UNIQUE (session_student_participant_id);


--
-- Name: student_class_enrollments student_class_enrollments_no_overlap; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_class_enrollments
    ADD CONSTRAINT student_class_enrollments_no_overlap EXCLUDE USING gist (student_id WITH =, daterange(effective_from, effective_until, '[)'::text) WITH &&);


--
-- Name: student_class_enrollments student_class_enrollments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_class_enrollments
    ADD CONSTRAINT student_class_enrollments_pkey PRIMARY KEY (id);


--
-- Name: student_guardian_relationships student_guardian_relationships_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_guardian_relationships
    ADD CONSTRAINT student_guardian_relationships_pkey PRIMARY KEY (id);


--
-- Name: student_identifiers student_identifiers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_identifiers
    ADD CONSTRAINT student_identifiers_pkey PRIMARY KEY (id);


--
-- Name: student_session_grooming_notes student_session_grooming_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_session_grooming_notes
    ADD CONSTRAINT student_session_grooming_notes_pkey PRIMARY KEY (id);


--
-- Name: student_session_grooming_notes student_session_grooming_notes_session_student_participant_id_u; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_session_grooming_notes
    ADD CONSTRAINT student_session_grooming_notes_session_student_participant_id_u UNIQUE (session_student_participant_id);


--
-- Name: student_status_history student_status_history_no_overlap; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_status_history
    ADD CONSTRAINT student_status_history_no_overlap EXCLUDE USING gist (student_id WITH =, daterange(effective_from, effective_until, '[)'::text) WITH &&);


--
-- Name: student_status_history student_status_history_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_status_history
    ADD CONSTRAINT student_status_history_pkey PRIMARY KEY (id);


--
-- Name: students students_nis_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_nis_unique UNIQUE (nis);


--
-- Name: students students_nisn_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_nisn_unique UNIQUE (nisn);


--
-- Name: students students_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_pkey PRIMARY KEY (id);


--
-- Name: students students_student_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_student_code_unique UNIQUE (student_code);


--
-- Name: subjects subjects_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_pkey PRIMARY KEY (id);


--
-- Name: subjects subjects_subject_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_subject_code_unique UNIQUE (subject_code);


--
-- Name: teaching_assignments teaching_assignments_assignment_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_assignment_code_unique UNIQUE (assignment_code);


--
-- Name: teaching_assignments teaching_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_pkey PRIMARY KEY (id);


--
-- Name: academic_transcript_signatories transcript_signatories_version_role_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_signatories
    ADD CONSTRAINT transcript_signatories_version_role_unique UNIQUE (academic_transcript_version_id, signatory_role);


--
-- Name: user_role_assignments user_role_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_role_assignments
    ADD CONSTRAINT user_role_assignments_pkey PRIMARY KEY (id);


--
-- Name: user_staff_links user_staff_links_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_staff_links
    ADD CONSTRAINT user_staff_links_pkey PRIMARY KEY (id);


--
-- Name: user_staff_links user_staff_links_staff_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_staff_links
    ADD CONSTRAINT user_staff_links_staff_id_unique UNIQUE (staff_id);


--
-- Name: user_staff_links user_staff_links_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_staff_links
    ADD CONSTRAINT user_staff_links_user_id_unique UNIQUE (user_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: academic_calendar_events_academic_year_id_start_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX academic_calendar_events_academic_year_id_start_at_index ON public.academic_calendar_events USING btree (academic_year_id, start_at);


--
-- Name: academic_calendar_events_class_id_start_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX academic_calendar_events_class_id_start_at_index ON public.academic_calendar_events USING btree (class_id, start_at);


--
-- Name: academic_calendar_events_organizational_unit_id_start_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX academic_calendar_events_organizational_unit_id_start_at_index ON public.academic_calendar_events USING btree (organizational_unit_id, start_at);


--
-- Name: academic_years_status_starts_on_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX academic_years_status_starts_on_index ON public.academic_years USING btree (status, starts_on);


--
-- Name: alerts_alert_rule_id_fingerprint_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX alerts_alert_rule_id_fingerprint_status_index ON public.alerts USING btree (alert_rule_id, fingerprint, status);


--
-- Name: attendance_period_locks_class_id_status_period_start_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX attendance_period_locks_class_id_status_period_start_index ON public.attendance_period_locks USING btree (class_id, status, period_start);


--
-- Name: audit_logs_actor_user_id_occurred_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_actor_user_id_occurred_at_index ON public.audit_logs USING btree (actor_user_id, occurred_at);


--
-- Name: audit_logs_correction_request_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_correction_request_id_index ON public.audit_logs USING btree (correction_request_id);


--
-- Name: audit_logs_entity_type_entity_id_occurred_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_entity_type_entity_id_occurred_at_index ON public.audit_logs USING btree (entity_type, entity_id, occurred_at);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: class_homeroom_assignments_class_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX class_homeroom_assignments_class_id_effective_from_index ON public.class_homeroom_assignments USING btree (class_id, effective_from);


--
-- Name: class_homeroom_assignments_staff_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX class_homeroom_assignments_staff_id_effective_from_index ON public.class_homeroom_assignments USING btree (staff_id, effective_from);


--
-- Name: class_session_groups_class_id_scope_role_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX class_session_groups_class_id_scope_role_index ON public.class_session_groups USING btree (class_id, scope_role);


--
-- Name: class_sessions_class_id_planned_start_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX class_sessions_class_id_planned_start_at_index ON public.class_sessions USING btree (class_id, planned_start_at);


--
-- Name: class_sessions_schedule_rule_start_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX class_sessions_schedule_rule_start_unique ON public.class_sessions USING btree (schedule_rule_id, planned_start_at) WHERE (schedule_rule_id IS NOT NULL);


--
-- Name: class_sessions_teaching_assignment_id_planned_start_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX class_sessions_teaching_assignment_id_planned_start_at_index ON public.class_sessions USING btree (teaching_assignment_id, planned_start_at);


--
-- Name: classes_academic_year_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX classes_academic_year_id_status_index ON public.classes USING btree (academic_year_id, status);


--
-- Name: correction_requests_entity_type_entity_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX correction_requests_entity_type_entity_id_status_index ON public.correction_requests USING btree (entity_type, entity_id, status);


--
-- Name: correction_requests_requested_by_user_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX correction_requests_requested_by_user_id_status_index ON public.correction_requests USING btree (requested_by_user_id, status);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: guardian_active_primary_channel_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX guardian_active_primary_channel_unique ON public.guardian_contact_channels USING btree (guardian_id, channel_type) WHERE ((is_primary = true) AND (active_until IS NULL));


--
-- Name: guardian_contact_channels_channel_type_normalized_value_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX guardian_contact_channels_channel_type_normalized_value_index ON public.guardian_contact_channels USING btree (channel_type, normalized_value);


--
-- Name: guardian_contact_channels_guardian_id_channel_type_active_until; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX guardian_contact_channels_guardian_id_channel_type_active_until ON public.guardian_contact_channels USING btree (guardian_id, channel_type, active_until);


--
-- Name: import_lineages_target_type_target_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX import_lineages_target_type_target_id_index ON public.import_lineages USING btree (target_type, target_id);


--
-- Name: import_row_errors_import_row_id_severity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX import_row_errors_import_row_id_severity_index ON public.import_row_errors USING btree (import_row_id, severity);


--
-- Name: import_rows_import_batch_id_row_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX import_rows_import_batch_id_row_status_index ON public.import_rows USING btree (import_batch_id, row_status);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: locations_record_status_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX locations_record_status_effective_from_index ON public.locations USING btree (record_status, effective_from);


--
-- Name: monthly_attendance_summaries_period_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX monthly_attendance_summaries_period_status_index ON public.monthly_attendance_summaries USING btree (period, status);


--
-- Name: monthly_student_attendance_snapshots_period_class_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX monthly_student_attendance_snapshots_period_class_id_index ON public.monthly_student_attendance_snapshots USING btree (period, class_id);


--
-- Name: monthly_student_attendance_snapshots_source_record_id_source_ch; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX monthly_student_attendance_snapshots_source_record_id_source_ch ON public.monthly_student_attendance_snapshots USING btree (source_record_id, source_checksum);


--
-- Name: organizational_units_record_status_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX organizational_units_record_status_effective_from_index ON public.organizational_units USING btree (record_status, effective_from);


--
-- Name: schedule_changes_source_session_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX schedule_changes_source_session_id_status_index ON public.schedule_changes USING btree (source_session_id, status);


--
-- Name: schedule_rule_groups_class_id_scope_role_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX schedule_rule_groups_class_id_scope_role_index ON public.schedule_rule_groups USING btree (class_id, scope_role);


--
-- Name: schedule_rules_teaching_assignment_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX schedule_rules_teaching_assignment_id_effective_from_index ON public.schedule_rules USING btree (teaching_assignment_id, effective_from);


--
-- Name: schedule_rules_weekday_workflow_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX schedule_rules_weekday_workflow_status_index ON public.schedule_rules USING btree (weekday, workflow_status);


--
-- Name: semester_subject_grades_semester_id_subject_id_workflow_status_; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX semester_subject_grades_semester_id_subject_id_workflow_status_ ON public.semester_subject_grades USING btree (semester_id, subject_id, workflow_status);


--
-- Name: session_student_participants_class_session_id_participant_statu; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX session_student_participants_class_session_id_participant_statu ON public.session_student_participants USING btree (class_session_id, participant_status);


--
-- Name: session_teacher_expected_primary_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX session_teacher_expected_primary_unique ON public.session_teacher_participations USING btree (class_session_id) WHERE (((role)::text = 'PRIMARY'::text) AND ((participation_status)::text = 'EXPECTED'::text));


--
-- Name: session_teacher_participations_class_session_id_participation_s; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX session_teacher_participations_class_session_id_participation_s ON public.session_teacher_participations USING btree (class_session_id, participation_status);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: staff_organizational_assignments_organizational_unit_id_effecti; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX staff_organizational_assignments_organizational_unit_id_effecti ON public.staff_organizational_assignments USING btree (organizational_unit_id, effective_from);


--
-- Name: staff_organizational_assignments_staff_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX staff_organizational_assignments_staff_id_effective_from_index ON public.staff_organizational_assignments USING btree (staff_id, effective_from);


--
-- Name: staff_record_status_active_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX staff_record_status_active_from_index ON public.staff USING btree (record_status, active_from);


--
-- Name: student_attendance_workflow_status_updated_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_attendance_workflow_status_updated_at_index ON public.student_attendance USING btree (workflow_status, updated_at);


--
-- Name: student_class_enrollments_class_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_class_enrollments_class_id_effective_from_index ON public.student_class_enrollments USING btree (class_id, effective_from);


--
-- Name: student_class_enrollments_student_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_class_enrollments_student_id_effective_from_index ON public.student_class_enrollments USING btree (student_id, effective_from);


--
-- Name: student_guardian_active_relationship_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX student_guardian_active_relationship_unique ON public.student_guardian_relationships USING btree (student_id, guardian_id, relationship_type) WHERE ((relationship_status)::text = 'ACTIVE'::text);


--
-- Name: student_guardian_relationships_guardian_id_relationship_status_; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_guardian_relationships_guardian_id_relationship_status_ ON public.student_guardian_relationships USING btree (guardian_id, relationship_status);


--
-- Name: student_guardian_relationships_student_id_relationship_status_i; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_guardian_relationships_student_id_relationship_status_i ON public.student_guardian_relationships USING btree (student_id, relationship_status);


--
-- Name: student_identifiers_active_nisn_value_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX student_identifiers_active_nisn_value_unique ON public.student_identifiers USING btree (identifier_value) WHERE (((record_status)::text = 'ACTIVE'::text) AND ((identifier_type)::text = 'NISN'::text));


--
-- Name: student_identifiers_active_student_type_no_scope_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX student_identifiers_active_student_type_no_scope_unique ON public.student_identifiers USING btree (student_id, identifier_type) WHERE (((record_status)::text = 'ACTIVE'::text) AND (identifier_scope IS NULL));


--
-- Name: student_identifiers_active_student_type_scope_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX student_identifiers_active_student_type_scope_unique ON public.student_identifiers USING btree (student_id, identifier_type, identifier_scope) WHERE (((record_status)::text = 'ACTIVE'::text) AND (identifier_scope IS NOT NULL));


--
-- Name: student_identifiers_identifier_type_identifier_value_record_sta; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_identifiers_identifier_type_identifier_value_record_sta ON public.student_identifiers USING btree (identifier_type, identifier_value, record_status);


--
-- Name: student_identifiers_student_id_identifier_type_record_status_in; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_identifiers_student_id_identifier_type_record_status_in ON public.student_identifiers USING btree (student_id, identifier_type, record_status);


--
-- Name: student_status_history_student_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_status_history_student_id_effective_from_index ON public.student_status_history USING btree (student_id, effective_from);


--
-- Name: student_status_history_student_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX student_status_history_student_id_status_index ON public.student_status_history USING btree (student_id, status);


--
-- Name: subjects_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subjects_status_index ON public.subjects USING btree (status);


--
-- Name: teaching_assignments_semester_id_class_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX teaching_assignments_semester_id_class_id_index ON public.teaching_assignments USING btree (semester_id, class_id);


--
-- Name: teaching_assignments_teacher_staff_id_effective_from_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX teaching_assignments_teacher_staff_id_effective_from_index ON public.teaching_assignments USING btree (teacher_staff_id, effective_from);


--
-- Name: user_role_assignments_scope_type_scope_key_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_role_assignments_scope_type_scope_key_index ON public.user_role_assignments USING btree (scope_type, scope_key);


--
-- Name: user_role_assignments_user_id_effective_from_effective_until_in; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_role_assignments_user_id_effective_from_effective_until_in ON public.user_role_assignments USING btree (user_id, effective_from, effective_until);


--
-- Name: user_staff_links_user_id_effective_from_effective_until_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_staff_links_user_id_effective_from_effective_until_index ON public.user_staff_links USING btree (user_id, effective_from, effective_until);


--
-- Name: audit_logs audit_logs_append_only; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER audit_logs_append_only BEFORE DELETE OR UPDATE ON public.audit_logs FOR EACH ROW EXECUTE FUNCTION public.prevent_audit_log_mutation();


--
-- Name: academic_calendar_events academic_calendar_events_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_calendar_events
    ADD CONSTRAINT academic_calendar_events_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE RESTRICT;


--
-- Name: academic_calendar_events academic_calendar_events_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_calendar_events
    ADD CONSTRAINT academic_calendar_events_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: academic_calendar_events academic_calendar_events_created_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_calendar_events
    ADD CONSTRAINT academic_calendar_events_created_by_user_id_foreign FOREIGN KEY (created_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: academic_calendar_events academic_calendar_events_organizational_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_calendar_events
    ADD CONSTRAINT academic_calendar_events_organizational_unit_id_foreign FOREIGN KEY (organizational_unit_id) REFERENCES public.organizational_units(id) ON DELETE RESTRICT;


--
-- Name: academic_calendar_events academic_calendar_events_updated_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_calendar_events
    ADD CONSTRAINT academic_calendar_events_updated_by_user_id_foreign FOREIGN KEY (updated_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_lines academic_transcript_lines_academic_transcript_version_id_foreig; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_lines
    ADD CONSTRAINT academic_transcript_lines_academic_transcript_version_id_foreig FOREIGN KEY (academic_transcript_version_id) REFERENCES public.academic_transcript_versions(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_lines academic_transcript_lines_semester_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_lines
    ADD CONSTRAINT academic_transcript_lines_semester_id_foreign FOREIGN KEY (semester_id) REFERENCES public.semesters(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_lines academic_transcript_lines_semester_subject_grade_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_lines
    ADD CONSTRAINT academic_transcript_lines_semester_subject_grade_id_foreign FOREIGN KEY (semester_subject_grade_id) REFERENCES public.semester_subject_grades(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_lines academic_transcript_lines_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_lines
    ADD CONSTRAINT academic_transcript_lines_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_signatories academic_transcript_signatories_academic_transcript_version_id_; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_signatories
    ADD CONSTRAINT academic_transcript_signatories_academic_transcript_version_id_ FOREIGN KEY (academic_transcript_version_id) REFERENCES public.academic_transcript_versions(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_signatories academic_transcript_signatories_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_signatories
    ADD CONSTRAINT academic_transcript_signatories_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_versions academic_transcript_versions_academic_transcript_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_versions
    ADD CONSTRAINT academic_transcript_versions_academic_transcript_id_foreign FOREIGN KEY (academic_transcript_id) REFERENCES public.academic_transcripts(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_versions academic_transcript_versions_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_versions
    ADD CONSTRAINT academic_transcript_versions_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_versions academic_transcript_versions_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_versions
    ADD CONSTRAINT academic_transcript_versions_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_versions academic_transcript_versions_published_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_versions
    ADD CONSTRAINT academic_transcript_versions_published_by_foreign FOREIGN KEY (published_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: academic_transcript_versions academic_transcript_versions_reviewed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcript_versions
    ADD CONSTRAINT academic_transcript_versions_reviewed_by_foreign FOREIGN KEY (reviewed_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: academic_transcripts academic_transcripts_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_transcripts
    ADD CONSTRAINT academic_transcripts_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: alert_actions alert_actions_actor_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alert_actions
    ADD CONSTRAINT alert_actions_actor_user_id_foreign FOREIGN KEY (actor_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: alert_actions alert_actions_alert_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alert_actions
    ADD CONSTRAINT alert_actions_alert_id_foreign FOREIGN KEY (alert_id) REFERENCES public.alerts(id) ON DELETE RESTRICT;


--
-- Name: alert_rules alert_rules_supersedes_rule_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alert_rules
    ADD CONSTRAINT alert_rules_supersedes_rule_fk FOREIGN KEY (supersedes_rule_id) REFERENCES public.alert_rules(id) ON DELETE RESTRICT;


--
-- Name: alerts alerts_alert_rule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alerts
    ADD CONSTRAINT alerts_alert_rule_id_foreign FOREIGN KEY (alert_rule_id) REFERENCES public.alert_rules(id) ON DELETE RESTRICT;


--
-- Name: alerts alerts_owner_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alerts
    ADD CONSTRAINT alerts_owner_user_id_foreign FOREIGN KEY (owner_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: alerts alerts_resolved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.alerts
    ADD CONSTRAINT alerts_resolved_by_foreign FOREIGN KEY (resolved_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: attendance_period_locks attendance_period_locks_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_period_locks
    ADD CONSTRAINT attendance_period_locks_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: attendance_period_locks attendance_period_locks_locked_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_period_locks
    ADD CONSTRAINT attendance_period_locks_locked_by_foreign FOREIGN KEY (locked_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: audit_logs audit_logs_actor_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_actor_user_id_foreign FOREIGN KEY (actor_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: class_homeroom_assignments class_homeroom_assignments_assigned_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_homeroom_assignments
    ADD CONSTRAINT class_homeroom_assignments_assigned_by_user_id_foreign FOREIGN KEY (assigned_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: class_homeroom_assignments class_homeroom_assignments_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_homeroom_assignments
    ADD CONSTRAINT class_homeroom_assignments_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: class_homeroom_assignments class_homeroom_assignments_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_homeroom_assignments
    ADD CONSTRAINT class_homeroom_assignments_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: class_session_groups class_session_groups_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_session_groups
    ADD CONSTRAINT class_session_groups_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: class_session_groups class_session_groups_class_session_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_session_groups
    ADD CONSTRAINT class_session_groups_class_session_id_foreign FOREIGN KEY (class_session_id) REFERENCES public.class_sessions(id) ON DELETE CASCADE;


--
-- Name: class_sessions class_sessions_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: class_sessions class_sessions_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.locations(id) ON DELETE RESTRICT;


--
-- Name: class_sessions class_sessions_rescheduled_from_session_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_rescheduled_from_session_id_foreign FOREIGN KEY (rescheduled_from_session_id) REFERENCES public.class_sessions(id) ON DELETE RESTRICT;


--
-- Name: class_sessions class_sessions_schedule_rule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_schedule_rule_id_foreign FOREIGN KEY (schedule_rule_id) REFERENCES public.schedule_rules(id) ON DELETE RESTRICT;


--
-- Name: class_sessions class_sessions_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE RESTRICT;


--
-- Name: class_sessions class_sessions_teaching_assignment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.class_sessions
    ADD CONSTRAINT class_sessions_teaching_assignment_id_foreign FOREIGN KEY (teaching_assignment_id) REFERENCES public.teaching_assignments(id) ON DELETE RESTRICT;


--
-- Name: classes classes_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE RESTRICT;


--
-- Name: classes classes_grade_level_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_grade_level_id_foreign FOREIGN KEY (grade_level_id) REFERENCES public.grade_levels(id) ON DELETE RESTRICT;


--
-- Name: classes classes_organizational_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_organizational_unit_id_foreign FOREIGN KEY (organizational_unit_id) REFERENCES public.organizational_units(id) ON DELETE RESTRICT;


--
-- Name: correction_requests correction_requests_requested_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.correction_requests
    ADD CONSTRAINT correction_requests_requested_by_user_id_foreign FOREIGN KEY (requested_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: correction_requests correction_requests_reviewed_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.correction_requests
    ADD CONSTRAINT correction_requests_reviewed_by_user_id_foreign FOREIGN KEY (reviewed_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: grade_levels grade_levels_organizational_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.grade_levels
    ADD CONSTRAINT grade_levels_organizational_unit_id_foreign FOREIGN KEY (organizational_unit_id) REFERENCES public.organizational_units(id) ON DELETE RESTRICT;


--
-- Name: guardian_contact_channels guardian_contact_channels_guardian_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guardian_contact_channels
    ADD CONSTRAINT guardian_contact_channels_guardian_id_foreign FOREIGN KEY (guardian_id) REFERENCES public.guardians(id) ON DELETE RESTRICT;


--
-- Name: guardian_contact_channels guardian_contact_channels_verified_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guardian_contact_channels
    ADD CONSTRAINT guardian_contact_channels_verified_by_user_id_foreign FOREIGN KEY (verified_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: import_batches import_batches_created_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_batches
    ADD CONSTRAINT import_batches_created_by_user_id_foreign FOREIGN KEY (created_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: import_files import_files_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_files
    ADD CONSTRAINT import_files_import_batch_id_foreign FOREIGN KEY (import_batch_id) REFERENCES public.import_batches(id) ON DELETE RESTRICT;


--
-- Name: import_lineages import_lineages_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_lineages
    ADD CONSTRAINT import_lineages_import_batch_id_foreign FOREIGN KEY (import_batch_id) REFERENCES public.import_batches(id) ON DELETE RESTRICT;


--
-- Name: import_lineages import_lineages_import_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_lineages
    ADD CONSTRAINT import_lineages_import_file_id_foreign FOREIGN KEY (import_file_id) REFERENCES public.import_files(id) ON DELETE RESTRICT;


--
-- Name: import_lineages import_lineages_import_file_id_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_lineages
    ADD CONSTRAINT import_lineages_import_file_id_import_batch_id_foreign FOREIGN KEY (import_file_id, import_batch_id) REFERENCES public.import_files(id, import_batch_id) ON DELETE RESTRICT;


--
-- Name: import_lineages import_lineages_import_row_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_lineages
    ADD CONSTRAINT import_lineages_import_row_id_foreign FOREIGN KEY (import_row_id) REFERENCES public.import_rows(id) ON DELETE RESTRICT;


--
-- Name: import_lineages import_lineages_import_row_id_import_file_id_import_batch_id_fo; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_lineages
    ADD CONSTRAINT import_lineages_import_row_id_import_file_id_import_batch_id_fo FOREIGN KEY (import_row_id, import_file_id, import_batch_id) REFERENCES public.import_rows(id, import_file_id, import_batch_id) ON DELETE RESTRICT;


--
-- Name: import_mappings import_mappings_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_mappings
    ADD CONSTRAINT import_mappings_import_batch_id_foreign FOREIGN KEY (import_batch_id) REFERENCES public.import_batches(id) ON DELETE RESTRICT;


--
-- Name: import_mappings import_mappings_reviewed_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_mappings
    ADD CONSTRAINT import_mappings_reviewed_by_user_id_foreign FOREIGN KEY (reviewed_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: import_row_errors import_row_errors_import_row_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_row_errors
    ADD CONSTRAINT import_row_errors_import_row_id_foreign FOREIGN KEY (import_row_id) REFERENCES public.import_rows(id) ON DELETE RESTRICT;


--
-- Name: import_row_errors import_row_errors_resolved_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_row_errors
    ADD CONSTRAINT import_row_errors_resolved_by_user_id_foreign FOREIGN KEY (resolved_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: import_rows import_rows_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_rows
    ADD CONSTRAINT import_rows_import_batch_id_foreign FOREIGN KEY (import_batch_id) REFERENCES public.import_batches(id) ON DELETE RESTRICT;


--
-- Name: import_rows import_rows_import_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_rows
    ADD CONSTRAINT import_rows_import_file_id_foreign FOREIGN KEY (import_file_id) REFERENCES public.import_files(id) ON DELETE RESTRICT;


--
-- Name: import_rows import_rows_import_file_id_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_rows
    ADD CONSTRAINT import_rows_import_file_id_import_batch_id_foreign FOREIGN KEY (import_file_id, import_batch_id) REFERENCES public.import_files(id, import_batch_id) ON DELETE RESTRICT;


--
-- Name: locations locations_organizational_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_organizational_unit_id_foreign FOREIGN KEY (organizational_unit_id) REFERENCES public.organizational_units(id) ON DELETE RESTRICT;


--
-- Name: monthly_attendance_summaries monthly_attendance_summaries_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_attendance_summaries
    ADD CONSTRAINT monthly_attendance_summaries_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: monthly_attendance_summaries monthly_attendance_summaries_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_attendance_summaries
    ADD CONSTRAINT monthly_attendance_summaries_import_batch_id_foreign FOREIGN KEY (import_batch_id) REFERENCES public.import_batches(id) ON DELETE RESTRICT;


--
-- Name: monthly_attendance_summaries monthly_attendance_summaries_published_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_attendance_summaries
    ADD CONSTRAINT monthly_attendance_summaries_published_by_user_id_foreign FOREIGN KEY (published_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_import_batch_id_foreign FOREIGN KEY (import_batch_id) REFERENCES public.import_batches(id) ON DELETE RESTRICT;


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_import_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_import_file_id_foreign FOREIGN KEY (import_file_id) REFERENCES public.import_files(id) ON DELETE RESTRICT;


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_import_row_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_import_row_id_foreign FOREIGN KEY (import_row_id) REFERENCES public.import_rows(id) ON DELETE RESTRICT;


--
-- Name: monthly_student_attendance_snapshots monthly_student_attendance_snapshots_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monthly_student_attendance_snapshots
    ADD CONSTRAINT monthly_student_attendance_snapshots_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: organizational_units organizational_units_parent_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizational_units
    ADD CONSTRAINT organizational_units_parent_unit_id_foreign FOREIGN KEY (parent_unit_id) REFERENCES public.organizational_units(id) ON DELETE RESTRICT;


--
-- Name: report_card_attendance_lines report_card_attendance_lines_report_card_version_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_attendance_lines
    ADD CONSTRAINT report_card_attendance_lines_report_card_version_id_foreign FOREIGN KEY (report_card_version_id) REFERENCES public.report_card_versions(id) ON DELETE RESTRICT;


--
-- Name: report_card_notes report_card_notes_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_notes
    ADD CONSTRAINT report_card_notes_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: report_card_notes report_card_notes_report_card_version_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_notes
    ADD CONSTRAINT report_card_notes_report_card_version_id_foreign FOREIGN KEY (report_card_version_id) REFERENCES public.report_card_versions(id) ON DELETE RESTRICT;


--
-- Name: report_card_notes report_card_notes_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_notes
    ADD CONSTRAINT report_card_notes_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: report_card_signatories report_card_signatories_report_card_version_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_signatories
    ADD CONSTRAINT report_card_signatories_report_card_version_id_foreign FOREIGN KEY (report_card_version_id) REFERENCES public.report_card_versions(id) ON DELETE RESTRICT;


--
-- Name: report_card_signatories report_card_signatories_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_signatories
    ADD CONSTRAINT report_card_signatories_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: report_card_subject_lines report_card_subject_lines_report_card_version_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_subject_lines
    ADD CONSTRAINT report_card_subject_lines_report_card_version_id_foreign FOREIGN KEY (report_card_version_id) REFERENCES public.report_card_versions(id) ON DELETE RESTRICT;


--
-- Name: report_card_subject_lines report_card_subject_lines_semester_subject_grade_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_subject_lines
    ADD CONSTRAINT report_card_subject_lines_semester_subject_grade_id_foreign FOREIGN KEY (semester_subject_grade_id) REFERENCES public.semester_subject_grades(id) ON DELETE RESTRICT;


--
-- Name: report_card_subject_lines report_card_subject_lines_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_subject_lines
    ADD CONSTRAINT report_card_subject_lines_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE RESTRICT;


--
-- Name: report_card_versions report_card_versions_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: report_card_versions report_card_versions_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: report_card_versions report_card_versions_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: report_card_versions report_card_versions_published_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_published_by_foreign FOREIGN KEY (published_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: report_card_versions report_card_versions_report_card_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_report_card_id_foreign FOREIGN KEY (report_card_id) REFERENCES public.report_cards(id) ON DELETE RESTRICT;


--
-- Name: report_card_versions report_card_versions_reviewed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_card_versions
    ADD CONSTRAINT report_card_versions_reviewed_by_foreign FOREIGN KEY (reviewed_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: report_cards report_cards_semester_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_cards
    ADD CONSTRAINT report_cards_semester_id_foreign FOREIGN KEY (semester_id) REFERENCES public.semesters(id) ON DELETE RESTRICT;


--
-- Name: report_cards report_cards_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.report_cards
    ADD CONSTRAINT report_cards_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: role_permissions role_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE RESTRICT;


--
-- Name: role_permissions role_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE RESTRICT;


--
-- Name: schedule_changes schedule_changes_applied_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_applied_by_user_id_foreign FOREIGN KEY (applied_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: schedule_changes schedule_changes_approved_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_approved_by_user_id_foreign FOREIGN KEY (approved_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: schedule_changes schedule_changes_original_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_original_teacher_id_foreign FOREIGN KEY (original_teacher_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: schedule_changes schedule_changes_related_session_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_related_session_id_foreign FOREIGN KEY (related_session_id) REFERENCES public.class_sessions(id) ON DELETE RESTRICT;


--
-- Name: schedule_changes schedule_changes_replacement_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_replacement_teacher_id_foreign FOREIGN KEY (replacement_teacher_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: schedule_changes schedule_changes_requested_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_requested_by_user_id_foreign FOREIGN KEY (requested_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: schedule_changes schedule_changes_source_session_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_changes
    ADD CONSTRAINT schedule_changes_source_session_id_foreign FOREIGN KEY (source_session_id) REFERENCES public.class_sessions(id) ON DELETE RESTRICT;


--
-- Name: schedule_rule_groups schedule_rule_groups_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rule_groups
    ADD CONSTRAINT schedule_rule_groups_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: schedule_rule_groups schedule_rule_groups_schedule_rule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rule_groups
    ADD CONSTRAINT schedule_rule_groups_schedule_rule_id_foreign FOREIGN KEY (schedule_rule_id) REFERENCES public.schedule_rules(id) ON DELETE CASCADE;


--
-- Name: schedule_rule_week_numbers schedule_rule_week_numbers_schedule_rule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rule_week_numbers
    ADD CONSTRAINT schedule_rule_week_numbers_schedule_rule_id_foreign FOREIGN KEY (schedule_rule_id) REFERENCES public.schedule_rules(id) ON DELETE CASCADE;


--
-- Name: schedule_rules schedule_rules_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rules
    ADD CONSTRAINT schedule_rules_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.locations(id) ON DELETE RESTRICT;


--
-- Name: schedule_rules schedule_rules_teaching_assignment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_rules
    ADD CONSTRAINT schedule_rules_teaching_assignment_id_foreign FOREIGN KEY (teaching_assignment_id) REFERENCES public.teaching_assignments(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_entered_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_entered_by_foreign FOREIGN KEY (entered_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_finalized_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_finalized_by_foreign FOREIGN KEY (finalized_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_responsible_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_responsible_staff_id_foreign FOREIGN KEY (responsible_staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_semester_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_semester_id_foreign FOREIGN KEY (semester_id) REFERENCES public.semesters(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_source_teaching_assignment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_source_teaching_assignment_id_foreign FOREIGN KEY (source_teaching_assignment_id) REFERENCES public.teaching_assignments(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE RESTRICT;


--
-- Name: semester_subject_grades semester_subject_grades_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semester_subject_grades
    ADD CONSTRAINT semester_subject_grades_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: semesters semesters_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.semesters
    ADD CONSTRAINT semesters_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE RESTRICT;


--
-- Name: session_student_participants session_student_participants_class_session_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_student_participants
    ADD CONSTRAINT session_student_participants_class_session_id_foreign FOREIGN KEY (class_session_id) REFERENCES public.class_sessions(id) ON DELETE RESTRICT;


--
-- Name: session_student_participants session_student_participants_removed_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_student_participants
    ADD CONSTRAINT session_student_participants_removed_by_user_id_foreign FOREIGN KEY (removed_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: session_student_participants session_student_participants_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_student_participants
    ADD CONSTRAINT session_student_participants_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: session_teacher_participations session_teacher_participations_class_session_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_teacher_participations
    ADD CONSTRAINT session_teacher_participations_class_session_id_foreign FOREIGN KEY (class_session_id) REFERENCES public.class_sessions(id) ON DELETE RESTRICT;


--
-- Name: session_teacher_participations session_teacher_participations_teacher_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.session_teacher_participations
    ADD CONSTRAINT session_teacher_participations_teacher_staff_id_foreign FOREIGN KEY (teacher_staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: staff_organizational_assignments staff_organizational_assignments_organizational_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.staff_organizational_assignments
    ADD CONSTRAINT staff_organizational_assignments_organizational_unit_id_foreign FOREIGN KEY (organizational_unit_id) REFERENCES public.organizational_units(id) ON DELETE RESTRICT;


--
-- Name: staff_organizational_assignments staff_organizational_assignments_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.staff_organizational_assignments
    ADD CONSTRAINT staff_organizational_assignments_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: student_attendance student_attendance_entered_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_entered_by_foreign FOREIGN KEY (entered_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: student_attendance student_attendance_finalized_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_finalized_by_foreign FOREIGN KEY (finalized_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: student_attendance student_attendance_session_student_participant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_session_student_participant_id_foreign FOREIGN KEY (session_student_participant_id) REFERENCES public.session_student_participants(id) ON DELETE RESTRICT;


--
-- Name: student_attendance student_attendance_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: student_class_enrollments student_class_enrollments_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_class_enrollments
    ADD CONSTRAINT student_class_enrollments_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: student_class_enrollments student_class_enrollments_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_class_enrollments
    ADD CONSTRAINT student_class_enrollments_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: student_guardian_relationships student_guardian_relationships_guardian_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_guardian_relationships
    ADD CONSTRAINT student_guardian_relationships_guardian_id_foreign FOREIGN KEY (guardian_id) REFERENCES public.guardians(id) ON DELETE RESTRICT;


--
-- Name: student_guardian_relationships student_guardian_relationships_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_guardian_relationships
    ADD CONSTRAINT student_guardian_relationships_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: student_identifiers student_identifiers_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_identifiers
    ADD CONSTRAINT student_identifiers_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: student_session_grooming_notes student_session_grooming_notes_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_session_grooming_notes
    ADD CONSTRAINT student_session_grooming_notes_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: student_session_grooming_notes student_session_grooming_notes_session_student_participant_id_f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_session_grooming_notes
    ADD CONSTRAINT student_session_grooming_notes_session_student_participant_id_f FOREIGN KEY (session_student_participant_id) REFERENCES public.session_student_participants(id) ON DELETE RESTRICT;


--
-- Name: student_session_grooming_notes student_session_grooming_notes_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_session_grooming_notes
    ADD CONSTRAINT student_session_grooming_notes_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: student_status_history student_status_history_actor_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_status_history
    ADD CONSTRAINT student_status_history_actor_user_id_foreign FOREIGN KEY (actor_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: student_status_history student_status_history_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.student_status_history
    ADD CONSTRAINT student_status_history_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE RESTRICT;


--
-- Name: teaching_assignments teaching_assignments_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE RESTRICT;


--
-- Name: teaching_assignments teaching_assignments_created_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_created_by_user_id_foreign FOREIGN KEY (created_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: teaching_assignments teaching_assignments_semester_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_semester_id_foreign FOREIGN KEY (semester_id) REFERENCES public.semesters(id) ON DELETE RESTRICT;


--
-- Name: teaching_assignments teaching_assignments_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE RESTRICT;


--
-- Name: teaching_assignments teaching_assignments_teacher_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_teacher_staff_id_foreign FOREIGN KEY (teacher_staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: teaching_assignments teaching_assignments_updated_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teaching_assignments
    ADD CONSTRAINT teaching_assignments_updated_by_user_id_foreign FOREIGN KEY (updated_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: user_role_assignments user_role_assignments_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_role_assignments
    ADD CONSTRAINT user_role_assignments_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE RESTRICT;


--
-- Name: user_role_assignments user_role_assignments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_role_assignments
    ADD CONSTRAINT user_role_assignments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: user_staff_links user_staff_links_linked_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_staff_links
    ADD CONSTRAINT user_staff_links_linked_by_user_id_foreign FOREIGN KEY (linked_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: user_staff_links user_staff_links_staff_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_staff_links
    ADD CONSTRAINT user_staff_links_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES public.staff(id) ON DELETE RESTRICT;


--
-- Name: user_staff_links user_staff_links_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_staff_links
    ADD CONSTRAINT user_staff_links_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

\unrestrict hgalMMskTUlwceQaf02l4i4L4242x7yvNJJKoS05Ro0uyTVELJjIL8tfY3wJBEI

