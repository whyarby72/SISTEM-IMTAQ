<?php

namespace Tests\Feature\Foundation;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabaseIdentityGuard;

final class TestDatabaseIdentityGuardTest extends TestCase
{
    public function test_ci_identity_is_accepted(): void
    {
        TestDatabaseIdentityGuard::assertConfiguredIdentity([
            'app_env' => 'testing',
            'driver' => 'pgsql',
            'url' => '',
            'host' => '127.0.0.1',
            'database' => 'imtaq_ci_test',
            'ci' => true,
        ]);

        $this->addToAssertionCount(1);
    }

    public function test_local_identity_is_accepted(): void
    {
        TestDatabaseIdentityGuard::assertConfiguredIdentity([
            'app_env' => 'testing',
            'driver' => 'pgsql',
            'url' => null,
            'host' => 'localhost',
            'database' => 'imtaq_test_codex_01',
            'ci' => false,
        ]);

        $this->addToAssertionCount(1);
    }

    #[DataProvider('rejectedConfigurationProvider')]
    public function test_unsafe_configuration_is_rejected(array $configuration): void
    {
        $this->expectException(LogicException::class);

        TestDatabaseIdentityGuard::assertConfiguredIdentity($configuration);
    }

    public static function rejectedConfigurationProvider(): iterable
    {
        $base = [
            'app_env' => 'testing',
            'driver' => 'pgsql',
            'url' => '',
            'host' => '127.0.0.1',
            'database' => 'imtaq_ci_test',
            'ci' => true,
        ];

        foreach ([
            'wrong environment' => ['app_env' => 'local'],
            'sqlite driver' => ['driver' => 'sqlite'],
            'redirecting URL' => ['url' => 'pgsql://user:password@127.0.0.1/imtaq'],
            'remote host' => ['host' => '10.0.0.5'],
            'pilot database' => ['database' => 'imtaq'],
            'staging database' => ['database' => 'imtaq_ci_staging'],
            'production database' => ['database' => 'imtaq_ci_production'],
            'prod database' => ['database' => 'imtaq_ci_prod'],
            'live database' => ['database' => 'imtaq_ci_live'],
            'unknown database' => ['database' => 'other_test'],
        ] as $label => $override) {
            yield $label => [array_merge($base, $override)];
        }
    }
}
