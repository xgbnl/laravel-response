<?php

declare(strict_types=1);

namespace Elephant\Response;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

final class ServiceProvider extends BaseServiceProvider
{
	public function register(): void
	{
		$this->app->bind(Reportable::class, JsonReport::class);
		$this->app->alias(ThrowableReport::class, 'Reportable');
	}

	public function boot(Router $router): void
	{
		$this->registerMiddleware('body.advice', $router);
	}

	protected function registerMiddleware(string $name, Router $router): void
	{
		$router->aliasMiddleware($name, FormatResponseBodyAdvice::class)
			->pushMiddlewareToGroup('api', $name);
	}
}
