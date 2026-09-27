#!/usr/bin/env python3
from __future__ import annotations

import json
import re
from pathlib import Path
from statistics import mean

ROOT = Path(__file__).resolve().parents[1]
QUEUE = ROOT / "codex" / "TASK_QUEUE.md"
NEXT = ROOT / "NEXT_ACTION.md"
REGISTRY = ROOT / "modules" / "MODULE_REGISTRY.json"
OUTPUT = ROOT / "PROJECT_PROGRESS.md"

ACADEMIC_PLANNING_UNITS = 13

WORKSTREAMS = [
    ("Foundation", [0]),
    ("Core Identity / RBAC / Audit", [1]),
    ("Academic Structure", [2]),
    ("Schedule / Session / Teacher Participation", [3, 4]),
    ("Student Attendance", [5, 6]),
    ("Semester Grades", [7]),
    ("Report Card / Transcript", [8]),
    ("KPI / Reporting", [9]),
    ("Alert / Data Quality", [10]),
    ("Historical Migration", [11]),
    ("Hardening / UAT / Pilot / Cutover", [12]),
]

SPRINT_RE = re.compile(r"^## Sprint\s+(\d+)\s+—\s+(.+)$")
TASK_RE = re.compile(
    r"^\|\s*(IMP-S(?P<sprint>\d+)-\d+)\s+(.+?)\s*\|\s*"
    r"(?P<status>[A-Z_]+)\s*\|\s*(?P<progress>\d{1,3})%\s*\|"
)
NEXT_RE = re.compile(r"\*\*Next task ID:\*\*\s*`([^`]+)`")


def rp(v: float) -> int:
    return int(v + 0.5)


def parse_queue(path: Path):
    tasks=[]; sprint_titles={}
    for raw in path.read_text(encoding="utf-8").splitlines():
        m=SPRINT_RE.match(raw)
        if m:
            sprint_titles[int(m.group(1))]=m.group(2).strip(); continue
        m=TASK_RE.match(raw)
        if not m: continue
        p=int(m.group("progress")); status=m.group("status")
        if not 0 <= p <= 100: raise SystemExit(f"Invalid progress {p}% for {m.group(1)}")
        if status=="DONE" and p!=100: raise SystemExit(f"DONE must be 100%: {m.group(1)}")
        if status in {"READY","NOT_STARTED"} and p!=0: raise SystemExit(f"{status} should be 0%: {m.group(1)}")
        if status.startswith("BLOCKED_") and p==100: raise SystemExit(f"Blocked cannot be 100%: {m.group(1)}")
        tasks.append({"id":m.group(1),"sprint":int(m.group("sprint")),"status":status,"progress":p})
    return tasks,sprint_titles


def current_task_id():
    m=NEXT_RE.search(NEXT.read_text(encoding="utf-8"))
    return m.group(1) if m else None


def workstream_for_sprint(sprint:int):
    for n,s in WORKSTREAMS:
        if sprint in s: return n,s
    return f"Sprint {sprint}",[sprint]


def implementation_from_queue(path: Path|None):
    if not path or not path.exists(): return 0.0
    tasks,_=parse_queue(path)
    return mean([t["progress"] for t in tasks]) if tasks else 0.0


def main():
    tasks,sprint_titles=parse_queue(QUEUE)
    if not tasks: raise SystemExit("No Academic implementation tasks found")
    by_id={t['id']:t for t in tasks}
    active=by_id.get(current_task_id())

    sprint_progress={}
    for s in sorted({t['sprint'] for t in tasks}):
        sprint_progress[s]=mean(t['progress'] for t in tasks if t['sprint']==s)

    workstream_progress=[]
    for name,sprints in WORKSTREAMS:
        vals=[t['progress'] for t in tasks if t['sprint'] in sprints]
        if vals: workstream_progress.append((name,mean(vals)))

    academic_impl=mean(t['progress'] for t in tasks)
    academic_equiv=sum(t['progress']/100 for t in tasks)
    academic_detailed=(ACADEMIC_PLANNING_UNITS+academic_equiv)/(ACADEMIC_PLANNING_UNITS+len(tasks))*100

    reg=json.loads(REGISTRY.read_text(encoding='utf-8'))
    domain_rows=[]
    for m in reg['modules']:
        planning=float(m.get('planning_progress',0))
        q=m.get('implementation_task_queue')
        impl=implementation_from_queue(ROOT/q) if q else 0.0
        delivery=(planning+impl)/2
        domain_rows.append((m['code'],m['name'],m['status'],planning,impl,delivery))
    system_planning=mean(r[3] for r in domain_rows)
    system_impl=mean(r[4] for r in domain_rows)
    system_delivery=mean(r[5] for r in domain_rows)

    active_ws_name=active_ws_value=None
    if active:
        active_ws_name,sprints=workstream_for_sprint(active['sprint'])
        vals=[t['progress'] for t in tasks if t['sprint'] in sprints]
        active_ws_value=mean(vals)

    lines=[
      "# PROJECT PROGRESS — MULTI-MODULE",
      "",
      "**Method:** see `project_management/PROGRESS_TRACKING.md`. Percentages are roadmap/governance tracking, not labor-hours or time remaining.",
      "",
      "## Headline",
      f"- **Academic planning:** 100%",
      f"- **Academic implementation:** {rp(academic_impl)}%",
      f"- **Academic detailed roadmap (legacy v1.x metric):** {rp(academic_detailed)}%",
      f"- **SISTEM IMTAQ domain planning coverage:** {rp(system_planning)}%",
      f"- **SISTEM IMTAQ domain implementation:** {rp(system_impl)}%",
      f"- **SISTEM IMTAQ overall delivery:** {rp(system_delivery)}%",
      f"- **Academic implementation tasks tracked:** {len(tasks)}",
      f"- **Shared Core architecture design:** 100% (`DESIGN_READY`; implementation 0% until task execution)",
      f"- **Parent Portal architecture design:** 100% (`DESIGN_READY_FUTURE`; implementation deferred)",
      f"- **Shared Communication architecture design:** 100% (`DESIGN_READY_FUTURE`; implementation deferred)",
      f"- **Codex safe-maintenance architecture design:** 100% (`DESIGN_LOCKED`; implementation enforcement begins in Sprint 0)",
      f"- **Codex checkpoint/timebox protocol design:** 100% (`DESIGN_LOCKED`; owner selects each local work horizon)",
    ]
    if active:
        lines += [
          f"- **Active task:** `{active['id']}` — {active['progress']}% ({active['status']})",
          f"- **Active workstream:** {active_ws_name} — {rp(active_ws_value)}%",
        ]
    lines += ["", "## Baseline Domain Progress", "", "| Domain | Status | Planning | Implementation | Delivery |", "|---|---|---:|---:|---:|"]
    for code,name,status,planning,impl,delivery in domain_rows:
        lines.append(f"| {name} (`{code}`) | {status} | {rp(planning)}% | {rp(impl)}% | {rp(delivery)}% |")

    lines += ["", "## Academic Workstream Progress", "", "| Workstream | Progress |", "|---|---:|"]
    for n,v in workstream_progress: lines.append(f"| {n} | {rp(v)}% |")

    lines += ["", "## Academic Sprint Progress", "", "| Sprint | Progress |", "|---|---:|"]
    for s,v in sprint_progress.items():
        title=sprint_titles.get(s,'')
        lines.append(f"| Sprint {s}"+(f" — {title}" if title else "")+f" | {rp(v)}% |")

    blocked=[t for t in tasks if t['status'].startswith('BLOCKED_')]
    lines += ["", "## Blocked Academic MVP Tasks", ""]
    lines += [f"- `{t['id']}` — {t['status']} — {t['progress']}%" for t in blocked] if blocked else ["- None."]

    lines += [
      "", "## Shared Workstream Status", "",
      "| Shared workstream | Status |",
      "|---|---|",
    ]
    for s in reg.get('shared_workstreams',[]): lines.append(f"| {s['name']} | {s['status']} |")

    lines += [
      "", "## Update Protocol",
      "1. Update verified task progress in the active task queue.",
      "2. If a module planning/status gate changes, update `modules/MODULE_REGISTRY.json` and its `STATUS.md`.",
      "3. Run `python scripts/update_project_progress.py`.",
      "4. Use this file for user-facing progress; do not invent percentages.", ""
    ]
    OUTPUT.write_text("\n".join(lines),encoding='utf-8')
    print(f"Updated {OUTPUT.relative_to(ROOT)}")
    print(f"Academic impl {rp(academic_impl)}% | System planning {rp(system_planning)}% | System impl {rp(system_impl)}% | System delivery {rp(system_delivery)}%")
    if active: print(f"Active {active['id']} {active['progress']}% | {active_ws_name} {rp(active_ws_value)}%")

if __name__=='__main__': main()
