<?php

namespace App\Shared\Platform\Deployment;

use Illuminate\Database\Connection;
use RuntimeException;

final class DatabaseTargetGuard
{
    /**
     * Verify both Laravel's resolved configuration and the PostgreSQL identity.
     *
     * This guard intentionally requires an explicit expected database, host,
     * and port so a cached Laravel configuration cannot be mistaken for a
     * shell-level environment override.
     *
     * @return array{configured: array<string, string>, actual: array<string, string>}
     */
    public function verify(Connection $connection, string $expectedDatabase, string $expectedHost, int $expectedPort): array
    {
        $config = $connection->getConfig();
        $configured = [
            'host' => (string) ($config['host'] ?? ''),
            'port' => (string) ($config['port'] ?? ''),
            'database' => (string) ($config['database'] ?? ''),
        ];

        $expected = [
            'host' => $expectedHost,
            'port' => (string) $expectedPort,
            'database' => $expectedDatabase,
        ];

        if ($configured !== $expected) {
            throw new RuntimeException(sprintf(
                'Database target mismatch: expected %s:%s/%s; Laravel resolved %s:%s/%s.',
                $expected['host'],
                $expected['port'],
                $expected['database'],
                $configured['host'],
                $configured['port'],
                $configured['database'],
            ));
        }

        $actualRow = $connection->selectOne(
            'select current_database() as database, inet_server_port() as port'
        );
        $actual = [
            'host' => $configured['host'],
            'port' => (string) ($actualRow->port ?? ''),
            'database' => (string) ($actualRow->database ?? ''),
        ];

        if ($actual !== $expected) {
            throw new RuntimeException(sprintf(
                'Database target mismatch: expected %s:%s/%s; PostgreSQL reports %s:%s/%s.',
                $expected['host'],
                $expected['port'],
                $expected['database'],
                $actual['host'],
                $actual['port'],
                $actual['database'],
            ));
        }

        return ['configured' => $configured, 'actual' => $actual];
    }
}
