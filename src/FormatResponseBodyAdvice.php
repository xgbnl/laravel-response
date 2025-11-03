<?php

declare(strict_types=1);

namespace Elephant\Response;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

readonly class FormatResponseBodyAdvice
{
	public function __construct(
		private Container $container,
	) {}

	/**
	 * @throws BindingResolutionException
	 */
	public function handle(Request $request, Closure $next): JsonResponse
	{

		$response = $next($request);

		if (property_exists($response, 'exception') && $response->exception instanceof Throwable) {
			return new JsonResponse($this->container->make('Reportable')->report($response->exception));
		}

		$reportable = $this->container->make(Reportable::class)
			->setRequest($request)
			->report($response);

		return new JsonResponse($reportable);
	}
}
