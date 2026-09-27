#!/usr/bin/env bash

set -euo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PROJECT_ROOT="$(cd "$APP_ROOT/../.." && pwd)"

cd "$APP_ROOT"

composer validate --strict
php artisan config:clear --ansi
php artisan route:list --except-vendor >/dev/null
php artisan test

python3 "$PROJECT_ROOT/scripts/check_project_structure.py"

echo "Foundation verification passed. No migration or deployment command was run."
