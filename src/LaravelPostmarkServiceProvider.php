<?php

namespace Bernskiold\LaravelPostmark;

use Bernskiold\LaravelPostmark\Http\Controllers\WebhookController;
use Bernskiold\LaravelPostmark\Http\Middleware\VerifyPostmarkWebhook;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class LaravelPostmarkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/postmark.php', 'postmark'
        );
    }

    public function boot(): void
    {
        AboutCommand::add('Laravel Postmark', fn () => [
            'Version' => InstalledVersions::getPrettyVersion('bernskiold/laravel-postmark'),
        ]);

        $this->publishes([
            __DIR__.'/../config/postmark.php' => config_path('postmark.php'),
        ], 'postmark-config');

        $this->registerRouteMacro();
    }

    /**
     * `Route::postmarkWebhooks('webhooks/postmark')` adds the endpoint. The
     * application selects the path and the route group.
     */
    protected function registerRouteMacro(): void
    {
        Router::macro('postmarkWebhooks', function (string $uri = 'postmark'): Route {
            /** @var Router $this */
            return $this->post($uri, WebhookController::class)
                ->middleware(VerifyPostmarkWebhook::class);
        });
    }
}
