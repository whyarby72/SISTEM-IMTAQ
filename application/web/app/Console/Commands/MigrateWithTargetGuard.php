<?php

namespace App\Console\Commands;

use App\Shared\Platform\Deployment\DatabaseTargetGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class MigrateWithTargetGuard extends Command
{
    protected $signature = 'migrate:guarded
        {database : Expected PostgreSQL database name}
        {--connection=pgsql : Laravel connection name}
        {--host=127.0.0.1 : Expected PostgreSQL host}
        {--port= : Expected PostgreSQL port}';

    protected $description = 'Run migrations only after verifying Laravel and PostgreSQL target identity';

    public function handle(DatabaseTargetGuard $guard): int
    {
        $connectionName = (string) $this->option('connection');
        $connection = DB::connection($connectionName);
        $port = $this->option('port') ?? $connection->getConfig('port');

        if (! is_numeric($port)) {
            $this->error('An explicit numeric --port is required for the target guard.');

            return self::FAILURE;
        }

        try {
            $report = $guard->verify(
                $connection,
                (string) $this->argument('database'),
                (string) $this->option('host'),
                (int) $port,
            );
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Target guard PASS: %s:%s/%s',
            $report['actual']['host'],
            $report['actual']['port'],
            $report['actual']['database'],
        ));

        return $this->call('migrate', [
            '--database' => $connectionName,
            '--force' => true,
        ]);
    }
}
