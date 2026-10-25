<?php

namespace Bernskiold\LaravelPostmark\Tests;

use Bernskiold\LaravelPostmark\LaravelPostmarkServiceProvider;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelPostmarkServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('postmark.webhooks.username', 'postmark');
        $app['config']->set('postmark.webhooks.password', 'secret');
    }

    protected function defineRoutes($router): void
    {
        /** @var Router $router */
        $router->postmarkWebhooks('webhooks/postmark');
    }
}
