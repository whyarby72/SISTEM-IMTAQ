<?php

use App\Models\User;
use App\Shared\Platform\Imports\Services\July2026HistoricalSummaryValidator;
use App\Shared\Platform\Imports\Services\July2026MasterStudentImportService;
use App\Shared\Platform\Imports\Services\July2026MonthlySummaryImportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('imtaq:validate-july-2026 {path} {--checksum=}', function (July2026HistoricalSummaryValidator $validator) {
    $result = $validator->validateFile($this->argument('path'), $this->option('checksum'));
    $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $result['valid'] ? 0 : 1;
})->purpose('Validate the July 2026 historical monthly attendance seed without importing canonical facts.');

Artisan::command('imtaq:import-july-2026 {path} {--checksum=}', function (July2026MonthlySummaryImportService $service) {
    $actor = User::query()->where('email', 'admin.pilot@example.test')->first();
    if ($actor === null) {
        $this->error('No admin actor available for import audit.');

        return 1;
    }
    $result = $service->import($actor, $this->argument('path'), $this->option('checksum'));
    $this->line(json_encode(['batch_id' => $result['batch']->id, 'batch_status' => $result['batch']->status, 'summary_rows_created' => count($result['rows']), 'canonical_session_facts_created' => 0], JSON_PRETTY_PRINT));

    return 0;
})->purpose('Import July 2026 monthly summary without creating sessions or student attendance facts.');

Artisan::command('imtaq:import-master-students-july-2026 {path}', function (July2026MasterStudentImportService $service) {
    $result = $service->import($this->argument('path'));
    $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return 0;
})->purpose('Import the July 2026 master student roster and class enrollments without importing attendance.');
