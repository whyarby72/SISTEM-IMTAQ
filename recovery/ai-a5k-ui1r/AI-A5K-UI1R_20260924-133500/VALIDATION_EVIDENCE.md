# Validation Evidence

- `php artisan test tests/Feature/Academic/AI/AiProviderConfigurationTest.php`: 7 tests / 29 assertions, PASS.
- `php artisan test tests/Feature/Academic/AI`: 38 tests / 140 assertions, PASS after tool-list serialization fix.
- `php -l resources/views/admin/system/ai-provider/index.blade.php`: PASS.
- Pint: PASS.
- `php artisan view:cache`: PASS.
- Browser verification: Model ID renders as a disabled select with `Pilih credential terlebih dahulu`; existing verified credential remains masked and Public AI remains OFF.
- Observed error reproduced from screenshot: HTTP 400 `Invalid type for 'tools': expected an array of tools, but got an object instead.` Fixed by `array_values()` normalization in verification and runtime consumers.
