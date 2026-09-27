# Change Impact — IMP-REPORT-EXPORT-001

- Added two read-only exports for the published July monthly report: CSV and PDF.
- Both use the same five stored monthly-summary rows; no recalculation changes, session expansion, or per-student facts.
- PDF uses a self-contained lightweight generator because no PDF package is installed in the application.
