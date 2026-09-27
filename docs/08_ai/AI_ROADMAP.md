# AI Roadmap v1.1

**Status:** `FUTURE / DEFERRED_FUTURE` implementation roadmap.

AI architecture is prepared now so domain services remain AI-ready, but AI is deliberately downstream of trustworthy transactions.

## Gate AI-A — Foundation ready
Prerequisites:
- authentication/RBAC operational;
- domain services expose structured authorized queries;
- audit infrastructure exists;
- thin `AIProvider` boundary + selected `OpenAIProvider` adapter and feature flags implemented;
- provider secrets configured server-side.

## Gate AI-B — Read-only assistant / Super Admin pilot
Implement:
- chat shell;
- selected OpenAI API adapter;
- AI RBAC permission catalog;
- initial grant: `SUPER_ADMIN` only for assistant + READ;
- read tool registry constrained by underlying business permission/scope;
- context minimization;
- AI interaction/tool/usage logs;
- read-only and AI-RBAC evaluation suite.

All other roles remain deny-by-default. No write tools enabled.

## Gate AI-C — Draft assistant
Prerequisites:
- relevant domain transaction workflows stable;
- structured draft schemas;
- preview/confirmation UX.

Enable draft preparation only.

## Gate AI-D — Controlled actions
Prerequisites:
- domain command is production-ready;
- explicit confirmation;
- idempotency/concurrency tests;
- action audit lineage;
- AI evals pass.

Enable only whitelisted low/medium-risk commands.

## Gate AI-E — Cross-domain assistant
After Tahfizh/Kesantrian/etc. domain boundaries and access policies are production-ready, add their read tools to the shared assistant. Cross-domain summaries remain derived and role-scoped.

## Provider decision
- Selected provider: **OpenAI API**.
- Self-hosted/local AI: **not in current target**.
- Multi-provider routing/fallback: **not required**.
- Core system must continue when OpenAI is disabled/unavailable.

## Explicit non-goals
- AI as database administrator;
- autonomous sanctioning/discipline;
- automatic report publication;
- psychological/medical diagnosis;
- scoring iman/ikhlas/inner spirituality;
- replacing approved institutional workflows;
- operating a local LLM/GPU inference server in the current roadmap;
- building provider-routing complexity without a future approved need.

## Gate AI-B2 — Optional role expansion
Only after the Super Admin pilot is stable may another role receive AI access. Expansion is a configuration/governance decision, not an application redesign. Require explicit RBAC grant, privacy/impact review, tool allowlist review and regression tests.
