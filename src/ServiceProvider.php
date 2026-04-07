<?php

declare(strict_types=1);

namespace Elephant\Response;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

final class ServiceProvider extends BaseServiceProvider
{
	public function register(): void
	{
		$this->app->bind(Reportable::class, JsonReport::class);
		$this->app->alias(ThrowableReport::class, 'Reportable');
	}
}
