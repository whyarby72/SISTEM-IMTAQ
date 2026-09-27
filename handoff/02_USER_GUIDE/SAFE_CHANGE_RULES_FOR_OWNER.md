# Safe Change Rules for the Project Owner

1. **One task/change at a time.** Avoid combining unrelated requests.
2. **Repository first.** Every session should begin by reading current repository state.
3. **No file guessing.** Never choose source files manually based on memory.
4. **No direct production editing.** Changes should travel through Git/release/staging.
5. **No silent database changes.** Schema evolution uses migrations; business corrections use domain workflows.
6. **Review impact class.** Small module-local changes should not unexpectedly become Shared Core/global changes.
7. **Demand tests.** A fix without a regression test is fragile when a test is practical.
8. **Demand rollback/disable path.** Especially before staging/production.
9. **Keep secrets outside Git.** API keys/passwords belong in server secret/environment storage.
10. **Do not activate future features by accident.** AI, Communication, Parent Portal and biometrics remain gated.
