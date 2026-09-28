<?php

namespace Tests;

use App\Providers\AppServiceProvider;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\TestDatabaseIdentityGuard;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $app['config']->set('app.env', 'testing');
        $app->instance('env', 'testing');
        TestDatabaseIdentityGuard::assertSafe($app);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.limiter', 'array');
        $app['config']->set('queue.default', 'sync');
        $app->forgetInstance('cache');
        $app->forgetInstance(RateLimiter::class);
        \Illuminate\Support\Facades\RateLimiter::clearResolvedInstance();
        (new AppServiceProvider($app))->boot();

        return $app;
    }
}
