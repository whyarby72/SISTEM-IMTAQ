# SOC-I1E Pre-Activation Database Backup Manifest

Database: `imtaq`
Host: `127.0.0.1:5432`
Backup time: 2026-09-19

- `PRE_ACTIVATION_PILOT.dump`: custom-format logical dump; SHA-256 `f4f74dc8dbfdfc94c70ec335771d49b4c0457a5811a85eba9a7ccaebaf929a0a`
- `PRE_ACTIVATION_schema.sql`: schema-only dump; SHA-256 `d3b6e88f18802ab07faeb11db197968a8329ffedea8aa2f230c2029a13a11a5f`
- `pg_restore --list`: PASS

No database restore was performed. The backup is retained for rollback/recovery review.

