<?php

namespace Elephant\Response;

use Closure;
use Elephant\Response\Attributes\CustomResponse;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionFunction;
use ReflectionAttribute;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JsonReport implements Reportable
{

	protected ?Request $request = null;

	public function report(Response|Throwable $response): array
	{
		$responseResult = match ($this->request->getMethod()) {
			'POST', 'PATCH', 'PUT' => [201, 'created'],
			'DELETE'               => [204, 'deleted'],
			default                => [200, 'success'],
		};

		[$statusCode, $message] = $responseResult;

		if (isset($this->request->route()->action['uses'])) {
			if (!is_null($reflector = $this->getReflectionAttr($this->request->route()->action['uses']))) {
				$concrete = $reflector->newInstance();

				if ($concrete->hasMessage()) {
					$message = $concrete->getMessage();
				}

				if ($concrete->hasStatusCode()) {
					$statusCode = $concrete->getStatusCode();
				}
			}
		}

		return $this->render($response->getContent(), $statusCode, $message);
	}

	private function render(mixed $content, int $status, string $message): array
	{
		$response = ['msg' => $message, 'code' => $status, 'data' => null];

		if (json_validate($content)) {
			$response['data'] = json_decode($content, true);
		} else if (is_string($content)) {
			$response['msg'] = $content;
		}

		return $response;
	}

	public function setRequest(Request $request): self
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * @return ReflectionAttribute<CustomResponse>
	 */
	protected function getReflectionAttr(Closure|string $uses): ?ReflectionAttribute
	{
		if ($uses instanceof Closure) {

			$reflectFunc = new ReflectionFunction($uses);

			if (!empty($reflectFunc->getAttributes(CustomResponse::class))) {
				return $reflectFunc->getAttributes(CustomResponse::class)[0];
			}

			return null;
		}

		if (preg_match('/^.+Controller@.+$/', $uses) === 1) {

			$haystack = explode('@', $uses);

			if (!empty($haystack) && count($haystack) === 2) {
				[$clazz, $method] = $haystack;

				$reflector = new ReflectionClass($clazz);

				if ($reflector->hasMethod($method) && !empty($reflector->getMethod($method)->getAttributes(CustomResponse::class))) {
					return $reflector->getMethod($method)->getAttributes(CustomResponse::class)[0];
				}
			}
		}

		return null;
	}
}
