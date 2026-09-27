# SISTEM IMTAQ Module Workspace

This folder is the canonical registry for domain boundaries in the multi-module SISTEM IMTAQ workspace.

The system is one modular monolith, not a collection of independent databases. Every student-facing domain links to the canonical `Student_ID`, but each domain owns its own transactions and business rules.

Read in this order when a change involves module boundaries:
1. `MODULE_REGISTRY.md`
2. `MODULE_DEPENDENCY_MAP.md`
3. `CROSS_MODULE_CONTRACTS.md`
4. `CHANGE_IMPACT_RULES.md`
5. the target module's `README.md`, `STATUS.md`, and `AGENTS.md`

Do not implement a module whose status is `NOT_PLANNED`. First complete its planning/onboarding contract and change the registry status through an explicit decision/update.
