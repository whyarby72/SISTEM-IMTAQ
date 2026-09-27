# Example — Bug Fix Without Knowing Files

Instead of:

> Edit AttendanceController.php and PermissionService.php.

Use:

> BUG: Wali Kelas 1B cannot finalize attendance. Expected: the active Wali Kelas can finalize its own class. Actual: Access denied. It started after the last update.

Then use `handoff/01_PROMPTS/05_BUG_FIX.txt`.

Codex is responsible for locating the real cause, limiting scope, adding regression tests and documenting the patch.
