# Project Progress Tracking v1.1 — Multi-Module

## User-facing requirement
Every Codex work-turn response ends with:
1. a concise progress block; and
2. 3–4 numbered next-step choices, option **1** primary.

## Canonical sources
- active implementation tasks: `codex/TASK_QUEUE.md` and future module task queues;
- module planning/status: `modules/MODULE_REGISTRY.json`;
- current task: `NEXT_ACTION.md`;
- generated snapshot: `PROJECT_PROGRESS.md`.

Run after progress-affecting work:

```bash
python scripts/update_project_progress.py
```

## Task progress
- `0%`: no accepted deliverable.
- `1–99%`: verified partial Definition-of-Done/acceptance evidence only.
- `100%`: `DONE` with required tests/evidence passing.
- `READY`/`NOT_STARTED`: normally 0%.
- blocked work may keep verified partial progress but cannot be 100%.

## Academic detailed roadmap
The current Academic-first queue retains the prior detailed metric:

`Academic detailed roadmap = completed P1–P13 planning-equivalent units + Academic delivery task equivalents / (13 + task count)`.

This is useful internally but is not the system-wide denominator.

## Eight-domain system progress
The system delivery denominator is the eight baseline student-development domains registered in `modules/MODULE_REGISTRY.json`:
Academic, Tahfizh, Kesantrian, Ruhiyah, Kepengasuhan, Bahasa, Kegiatan & Kompetensi, Administratif & Layanan.

For each domain:
- `Planning %` comes from the registry.
- `Implementation %` comes from its registered implementation task queue; if no implementation queue exists, implementation is 0%.
- `Domain delivery % = (Planning % + Implementation %) / 2`.

System measures:

```text
System planning coverage     = mean(domain planning %)
System implementation        = mean(domain implementation %)
System overall delivery      = mean(domain delivery %)
                             = (System planning coverage + System implementation) / 2
```

This is a governance/roadmap tracking convention, not labor-hour weighting. Shared Core, Platform, Reporting, AI and Communication are shown separately and excluded from the eight-domain denominator to avoid double counting cross-cutting work.

At workspace v1.3 baseline:
- Academic planning = 100%; Academic implementation = 0%.
- Seven other baseline domains planning/implementation = 0%.
- System planning coverage = 12.5% → shown as 13%.
- System implementation = 0%.
- System overall delivery = 6.25% → shown as 6%.

## Active workstream/module progress
For the Academic-first queue, workstream progress remains the arithmetic mean of tasks in mapped sprint(s). Academic implementation progress is the mean of all MVP Sprint 0–12 task progress because those tasks deliver the current Academic MVP including its required shared foundation.

## Required end-of-turn format
Example:

```text
Progress
- Task IMP-S5-002: 70%
- Workstream Student Attendance: 54%
- Modul Akademik — implementasi: 31%
- SISTEM IMTAQ — implementasi: 4%
- SISTEM IMTAQ — keseluruhan delivery: 10%

Pilihan berikutnya:
1. [Rekomendasi utama] ...
2. ...
3. ...
4. ...
```

When useful, also show domain planning coverage or a shared-platform percentage. Never present the percentage as calendar time remaining.
