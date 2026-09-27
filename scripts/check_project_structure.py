#!/usr/bin/env python3
from pathlib import Path
import json,re,sys
ROOT=Path(__file__).resolve().parents[1]
required=[
 'AGENTS.md','START_HERE.md','00_CODEX_HANDOFF_START_HERE.md','CODEX_FIRST_PROMPT.txt','PROJECT_STATUS.md','PROJECT_PROGRESS.md','NEXT_ACTION.md',
 'handoff/README.md','handoff/00_START/COPY_PASTE_FIRST_PROMPT.txt','handoff/01_PROMPTS/00_PROMPT_INDEX.md','handoff/01_PROMPTS/19_CHECKPOINT_CHOICE.txt','handoff/01_PROMPTS/20_SAFE_CHECKPOINT_NOW.txt','handoff/01_PROMPTS/21_QUOTA_EFFICIENT_START.txt','handoff/01_PROMPTS/22_QUOTA_EFFICIENT_CONTINUE.txt','handoff/02_USER_GUIDE/NON_PROGRAMMER_CODEX_OPERATING_GUIDE.md','handoff/02_USER_GUIDE/CHECKPOINT_CHOICE_GUIDE.md','handoff/02_USER_GUIDE/CODEX_QUOTA_EFFICIENT_GUIDE.md','handoff/03_CHECKLISTS/SAFE_CHECKPOINT_CHECKLIST.md','handoff/05_REFERENCE/HANDOFF_MANIFEST.json',
 'modules/MODULE_REGISTRY.md','modules/MODULE_REGISTRY.json','modules/MODULE_DEPENDENCY_MAP.md',
 'modules/CROSS_MODULE_CONTRACTS.md','modules/CHANGE_IMPACT_RULES.md',
 'shared/README.md','shared/core/README.md','shared/platform/README.md','shared/reporting/README.md','shared/ai/README.md','shared/communication/README.md','shared/parent_portal/README.md',
 'codex/TASK_QUEUE.md','codex/SYSTEM_TASK_QUEUE.md','codex/NEXT_STEP_RESOLUTION.md','codex/CURRENT_TASK_CONTEXT.md','codex/CONTEXT_ROUTER.md','codex/MODEL_AND_REASONING_POLICY.md','codex/TASK_CONTEXTS/IMP-S0-001.md',
 'project_management/PROGRESS_TRACKING.md','project_management/MODULE_ROADMAP.md','project_management/CHANGE_IMPACT_REGISTER.md',
 'templates/CHANGE_IMPACT_TEMPLATE.md','templates/MODULE_ONBOARDING_TEMPLATE.md',
 'docs/00_governance/IMTAQ_CORE_ENGINE_BASE_SOURCE_v1.0.md',
 'docs/00_governance/POLICY_PENDING_REGISTER.md','docs/00_governance/MVP_FUTURE_SUPERSEDED.md',
 'docs/01_scope/PRODUCT_SCOPE.md','docs/01_scope/DOMAIN_MODEL.md',
 'docs/02_architecture/ARCHITECTURE_DECISIONS.md','docs/02_architecture/POSTGRESQL_SCHEMA.md',
 'docs/02_architecture/RBAC_MATRIX.md','docs/02_architecture/WORKFLOW_STATE_MACHINES.md','docs/02_architecture/BUSINESS_RULES.md','docs/02_architecture/SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md',
 'docs/03_operations/OPERATIONAL_SOP.md','docs/04_analytics/KPI_DICTIONARY.md','docs/04_analytics/ALERT_RULES.md',
 'docs/05_migration/MIGRATION_CONTRACT.md','docs/06_testing/UAT_MATRIX.md','docs/07_implementation/CODEX_BUILD_INSTRUCTIONS.md','docs/07_implementation/CODEX_CHECKPOINT_TIMEBOX_PROTOCOL.md','docs/07_implementation/CODEX_QUOTA_EFFICIENCY_PROTOCOL.md',
 'docs/08_ai/AI_ARCHITECTURE.md','docs/08_ai/AI_RBAC_ACCESS_POLICY.md','docs/08_ai/AI_PROVIDER_AND_SECRETS.md','docs/08_ai/AI_TOOL_REGISTRY.md','docs/08_ai/AI_WRITE_POLICY.md',
 'docs/09_communication/COMMUNICATION_ARCHITECTURE.md','docs/09_communication/GUARDIAN_CONTACT_AND_CONSENT.md',
 'docs/09_communication/WHATSAPP_PROVIDER_ARCHITECTURE.md','docs/09_communication/PARENT_REPORT_DELIVERY_WORKFLOW.md',
 'docs/09_communication/INSTITUTIONAL_BROADCAST_REMINDER_ACTION_REQUEST.md','docs/09_communication/AUDIENCE_AND_GROUP_REGISTRY.md','docs/09_communication/TEACHER_AVAILABILITY_CONFIRMATION.md',
 'docs/09_communication/COMMUNICATION_UAT_MATRIX.md',
 'docs/10_shared_core/SHARED_CORE_ARCHITECTURE.md','docs/10_shared_core/CORE_DATA_MODEL.md','docs/10_shared_core/CORE_POSTGRESQL_CONTRACT.md',
 'docs/10_shared_core/GUARDIAN_MASTER.md','docs/10_shared_core/AUTH_RBAC_AUDIT.md','docs/10_shared_core/CORE_SERVICE_CONTRACTS.md','docs/10_shared_core/CORE_UAT_MATRIX.md',
 'docs/11_parent_portal/PARENT_PORTAL_ARCHITECTURE.md','docs/11_parent_portal/GUARDIAN_ACCOUNT_LINKING.md','docs/11_parent_portal/PARENT_PORTAL_AUTHORIZATION.md','docs/11_parent_portal/PARENT_PORTAL_UAT_MATRIX.md',
]
missing=[p for p in required if not (ROOT/p).exists()]
if missing:
 print('FAIL missing:'); [print(' -',p) for p in missing]; sys.exit(1)
reg=json.loads((ROOT/'modules/MODULE_REGISTRY.json').read_text(encoding='utf-8'))
mods=reg.get('modules',[])
if len(mods)!=8:
 print(f"FAIL expected 8 baseline domain modules, found {len(mods)}"); sys.exit(1)
codes={m['code'] for m in mods}
expected={'ACADEMIC','TAHFIZH','KESANTRIAN','RUHIYAH','KEPENGASUHAN','BAHASA','KEGIATAN_KOMPETENSI','ADMINISTRATIF_LAYANAN'}
if codes!=expected:
 print('FAIL module codes mismatch',codes); sys.exit(1)
for m in mods:
 for f in ['README.md','STATUS.md','AGENTS.md']:
  if not (ROOT/m['path']/f).exists():
   print('FAIL missing module file',m['path'],f); sys.exit(1)
next_text=(ROOT/'NEXT_ACTION.md').read_text(encoding='utf-8')
m=re.search(r"\*\*Next task ID:\*\*\s*`([^`]+)`",next_text)
if not m:
 print('FAIL NEXT_ACTION has no task id'); sys.exit(1)
queue=(ROOT/'codex/TASK_QUEUE.md').read_text(encoding='utf-8')
if m.group(1) not in queue:
 print('FAIL next task not found in queue:',m.group(1)); sys.exit(1)
print(f"PASS: multi-module project structure valid; domains={len(mods)}; current task={m.group(1)}")
