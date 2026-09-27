<?php

namespace Tests\Feature\Academic\AI;

use App\Shared\Platform\Deployment\DatabaseTargetGuard;
use Illuminate\Database\Connection;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DatabaseTargetGuardTest extends TestCase
{
    public function test_guard_rejects_cached_laravel_target_mismatch(): void
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getConfig')->once()->andReturn([
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'imtaq',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Database target mismatch');

        app(DatabaseTargetGuard::class)->verify($connection, 'imtaq_a5k_disposable', '127.0.0.1', 55433);
    }

    public function test_guard_requires_both_resolved_and_postgresql_identity_to_match(): void
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getConfig')->once()->andReturn([
            'host' => '127.0.0.1',
            'port' => 55433,
            'database' => 'imtaq_a5k_disposable',
        ]);
        $connection->shouldReceive('selectOne')->once()->andReturn((object) [
            'database' => 'imtaq',
            'port' => 5432,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Database target mismatch');

        app(DatabaseTargetGuard::class)->verify($connection, 'imtaq_a5k_disposable', '127.0.0.1', 55433);
    }
}
