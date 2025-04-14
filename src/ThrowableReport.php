<?php

namespace Elephant\Response;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;

readonly class ThrowableReport implements Reportable
{
	public function __construct(private Application $app) {}

	public function report(Response|Throwable $response): array
	{
		$throwable = $this->wrap($response);

		$struct = [
			'msg' 	=> $throwable->getMessage(),
			'code'  => $throwable->getCode(),
		];

		if ($this->renderThrowableTrace() && $throwable->getCode() === 500) {
			return array_merge($struct, [
				'exception' => get_class($throwable->getPrevious()),
				'file'      => $throwable->getFile(),
				'line'      => $throwable->getLine(),
				'trace'     => Collection::make($throwable->getPrevious()->getTrace())
					->map(fn(array $trace): array => Arr::except($trace, ['args', 'type'])),
			]);
		}

		return array_merge($struct, ['data' => null]);
	}

	protected function wrap(Throwable $throwable): Throwable
	{

		if ($throwable instanceof QueryException) {
			return new Exception($throwable->getMessage(), 500, $throwable);
		}

		$errors = match (true) {
			$throwable instanceof MissingAbilityException                                               => [$throwable->getMessage(), 403],
			$throwable instanceof AuthenticationException                                               => [$throwable->getMessage(), 401],
			$throwable instanceof MethodNotAllowedException                                             => [$throwable->getMessage(), 405],
			$throwable instanceof ValidationException                                                   => [$throwable->validator->errors()->first(), 422],
			$throwable instanceof HttpException                                                         => [$throwable->getMessage(), $throwable->getStatusCode()],
			$throwable instanceof ModelNotFoundException || $throwable instanceof NotFoundHttpException => [$throwable->getMessage(), 404],
			default                                                                                     => [$throwable->getMessage(), $throwable->getCode() >= 500 || $throwable->getCode() === 0 ? 500 : $throwable->getCode()]
		};

		return new Exception(current($errors), next($errors), $throwable);
	}

	protected function renderThrowableTrace(): bool
	{
		return method_exists($this->app, 'isLocal') && $this->app->isLocal() && $this->app->get('config')->get('app.debug');
	}
}
