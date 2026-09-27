# SISTEM IMTAQ — CR-S.1 Durable Recovery Baseline

Project: SISTEM IMTAQ  
Gate: CR-S.1  
Baseline source: CR-S PASS_SNAPSHOT

Application root:

`/Users/afradadmedia/DATA/PTAFRADAD/CHATGPTLOCAL/SISTEM-IMTAQ/application/web`

Snapshot file: `IMTAQ_SOURCE_ROLLBACK_CR-S_20260918-062414.tar.gz`  
Snapshot SHA-256: `929202d3253f539cbc418a8f44997ce9276795cdd6ae2fe4b2512d6c17f46e2a`

Manifest file: `IMTAQ_SOURCE_ROLLBACK_CR-S_20260918-062414.sha256`  
Manifest SHA-256: `568294a64733aab7bec3757f7810466b65bea82cca10a31edfc6b41b20042ea3`

Metadata file: `IMTAQ_SOURCE_ROLLBACK_CR-S_20260918-062414.METADATA.txt`

Baseline included file count: 379

Created/copied at: 2026-09-18 (Asia/Jakarta)  
Durable recovery root: `/Users/afradadmedia/DATA/PTAFRADAD/CHATGPTLOCAL/SISTEM-IMTAQ/recovery/cr-s/CR-S_20260918-062414/`

## Purpose

Rollback baseline before canonical attendance semantic source changes.

## Restrictions

- Do not treat this archive as a database backup.
- `.env` and secrets are intentionally excluded.
- `vendor`, `node_modules`, and runtime artifacts are intentionally excluded.
- PostgreSQL data is not included.
- Restore into a separate recovery directory first.
- Overwrite working source only after explicit human approval.

## Restore procedure

1. Verify the archive checksum.
2. Extract into a separate recovery directory.
3. Verify restored files against the manifest.
4. Compare intended files against working source.
5. Only then perform explicitly approved restoration.

CR-S.1 is a source-recovery baseline only. It does not authorize migration, database, RBAC, semantic, AI, or production changes.
