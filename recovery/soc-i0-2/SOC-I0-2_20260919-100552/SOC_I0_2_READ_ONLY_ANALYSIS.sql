-- SOC-I0.2 evidence queries only. Run inside a READ ONLY transaction and ROLLBACK.
-- No query in this file performs INSERT, UPDATE, DELETE, DDL, import, or restore.
select current_database(), current_schema(), inet_server_addr(), inet_server_port(), version(), current_setting('TimeZone'), current_setting('transaction_read_only');
select migration, batch from migrations order by id;
select substr(md5(cs.id::text), 1, 12) as session_ref, cs.session_status, cs.session_source,
       cs.planned_start_at, cs.created_at, cs.updated_at,
       (cs.schedule_rule_id is not null) as has_schedule_rule,
       (select count(*) from schedule_changes sc where sc.source_session_id = cs.id or sc.related_session_id = cs.id) as schedule_change_count,
       (select string_agg(distinct sc.change_type, ',' order by sc.change_type) from schedule_changes sc where sc.source_session_id = cs.id or sc.related_session_id = cs.id) as change_types,
       (select count(*) from class_session_groups g where g.class_session_id = cs.id) as group_count,
       (select count(*) from session_teacher_participations tp where tp.class_session_id = cs.id) as teacher_participation_count,
       (select count(*) from student_attendance a join session_student_participants p on p.id = a.session_student_participant_id where p.class_session_id = cs.id) as attendance_count,
       exists (select 1 from import_lineages il where il.target_id::text = cs.id::text) as import_lineage_match
from class_sessions cs
where cs.session_status = 'CANCELLED'
  and not exists (select 1 from session_student_participants p where p.class_session_id = cs.id)
  and not exists (select 1 from session_teacher_participations tp where tp.class_session_id = cs.id)
  and not exists (select 1 from student_attendance a join session_student_participants p2 on p2.id = a.session_student_participant_id where p2.class_session_id = cs.id)
order by cs.created_at;
select session_status, count(*) from class_sessions group by session_status order by session_status;
select table_name from information_schema.tables where table_schema = 'public' and table_name in ('attendance_source_certifications', 'class_lineage_mappings');
select column_name, is_nullable, column_default from information_schema.columns where table_schema = 'public' and table_name = 'session_student_participants' and column_name in ('is_required', 'eligibility_status', 'non_eligible_reason');
