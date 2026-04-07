<?php

declare(strict_types=1);

namespace Elephant\Response;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Routing\Router;

final class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Reportable::class, JsonReport::class);
        $this->app->alias(ThrowableReport::class, 'Reportable');
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('body.advice', FormatResponseBodyAdvice::class)
            ->pushMiddlewareToGroup('api', 'body.advice');
    }
}
