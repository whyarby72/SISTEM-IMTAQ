# Change Manifest — IMP-REPORT-EXPORT-001

- Routes: `monthly-reports/july-2026.csv` and `monthly-reports/july-2026.pdf`.
- UI: `Unduh CSV` and `Unduh PDF rapi` buttons on the Waka report page.
- CSV verified with five class rows and total 1.126 present.
- PDF verified structurally with `pdfinfo`: one A4 page, 842x595 points, valid trailer/xref.
- PDF raster rendering was attempted; local Poppler stopped on a Fontconfig environment error, so no PNG visual artifact was produced.
