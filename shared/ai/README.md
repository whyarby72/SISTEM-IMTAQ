# Shared IMTAQ AI — DESIGN READY, IMPLEMENTATION FUTURE

Authoritative AI architecture currently lives under `docs/08_ai/`.

The AI layer is shared across modules and optional. **OpenAI API is the selected production provider.** Domain modules do not call OpenAI directly; they use the shared AI service boundary. AI tools invoke authorized domain services/semantic services, and writes use allowlisted domain commands with human confirmation and audit.

AI/provider outages must not block normal SISTEM IMTAQ transactions.

Initial rollout is `SUPER_ADMIN` only for AI Assistant/READ via RBAC permission. All other roles are deny-by-default. This restriction is configuration/governance, not a hard-coded role branch; future grants remain constrained by ordinary domain permissions/scopes.

Self-hosted/local AI is outside the current target. OpenAI outages or missing API credentials degrade AI only and must never stop normal domain transactions.
