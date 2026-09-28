<?php

namespace Tests\Support;

use Illuminate\Contracts\Foundation\Application;
use LogicException;

final class TestDatabaseIdentityGuard
{
    private const CI_DATABASE = 'imtaq_ci_test';

    private const PROTECTED_MARKERS = [
        'pilot',
        'staging',
        'production',
        'prod',
        'live',
    ];

    /**
     * Validate the non-secret configuration before opening the test connection.
     *
     * @param array{app_env:string,driver:string,url:?string,host:string,database:string,ci:bool} $configuration
     */
    public static function assertConfiguredIdentity(array $configuration): void
    {
        if ($configuration['app_env'] !== 'testing') {
            throw new LogicException('Test database guard rejected APP_ENV; testing is required.');
        }

        if ($configuration['driver'] !== 'pgsql') {
            throw new LogicException('Test database guard rejected the database driver; pgsql is required.');
        }

        if ($configuration['url'] !== null && $configuration['url'] !== '') {
            throw new LogicException('Test database guard rejected DB_URL; redirects are not allowed.');
        }

        $host = strtolower(trim($configuration['host']));
        if (! in_array($host, ['127.0.0.1', 'localhost', 'postgres'], true)) {
            throw new LogicException('Test database guard rejected the database host.');
        }

        self::assertAllowedDatabaseName($configuration['database'], $configuration['ci']);
    }

    public static function assertAllowedDatabaseName(string $database, bool $ci): void
    {
        $normalized = strtolower(trim($database));

        if ($normalized === 'imtaq') {
            throw new LogicException('Test database guard rejected the protected pilot identity.');
        }

        foreach (self::PROTECTED_MARKERS as $marker) {
            if (str_contains($normalized, $marker)) {
                throw new LogicException('Test database guard rejected a protected environment marker.');
            }
        }

        $allowed = $ci
            ? $normalized === self::CI_DATABASE
            : preg_match('/^imtaq_test_[a-z0-9][a-z0-9_-]*$/', $normalized) === 1;

        if (! $allowed) {
            throw new LogicException('Test database guard rejected the database naming contract.');
        }
    }

    public static function assertSafe(Application $app): array
    {
        $connection = (string) $app['config']->get('database.default');
        $pgsql = (array) $app['config']->get('database.connections.pgsql', []);
        $ci = strtolower((string) ($_SERVER['CI'] ?? $_ENV['CI'] ?? getenv('CI') ?: '')) === 'true';

        self::assertConfiguredIdentity([
            'app_env' => (string) $app['config']->get('app.env'),
            'driver' => $connection,
            'url' => $pgsql['url'] ?? null,
            'host' => (string) ($pgsql['host'] ?? ''),
            'database' => (string) ($pgsql['database'] ?? ''),
            'ci' => $ci,
        ]);

        $database = $app->make('db');
        $resolved = $database->connection('pgsql');

        if ($resolved->getDriverName() !== 'pgsql') {
            throw new LogicException('Test database guard rejected the resolved connection driver.');
        }

        $metadata = (array) $resolved->selectOne(
            'select current_database() as database_name, current_user as user_name, '
            .'current_setting(\'server_version_num\') as server_version_num, '
            .'current_setting(\'server_version\') as server_version'
        );

        $actualDatabase = strtolower((string) ($metadata['database_name'] ?? ''));
        if ($actualDatabase !== strtolower((string) $pgsql['database'])) {
            throw new LogicException('Test database guard rejected a database identity mismatch.');
        }

        if ($ci && (string) ($metadata['server_version_num'] ?? '') !== '180006') {
            throw new LogicException('Test database guard rejected a PostgreSQL version mismatch.');
        }

        return [
            'driver' => 'pgsql',
            'database' => $actualDatabase,
            'host' => (string) ($pgsql['host'] ?? ''),
            'server_version' => (string) ($metadata['server_version'] ?? ''),
        ];
    }
}
