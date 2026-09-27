<?php

namespace Tests;

use App\Providers\AppServiceProvider;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        // Test data must never use the pilot PostgreSQL connection, even when
        // a developer has a cached application configuration locally.
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.url', null);
        $app['config']->set('app.env', 'testing');
        $app->instance('env', 'testing');
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
