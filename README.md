### RESTFul style JSON responder.

> Support laravel.

#### install

```shell
composer require xgbnl/response dev-main
```

#### GET Method.

```json
{
  "msg": "success",
  "code": 200,
  "data": null
}
```

#### POST、PATCH Methods.

```json
{
  "msg": "created",
  "code": 201,
  "data": null
}
```

#### DELETE Method.

```json
{
  "msg": "deleted",
  "code": 204,
  "data": null
}
```
## Register
open your project file `bootstrap/app.php`
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: static function (): void {
           // Register your custom route file in this area.
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
         // Register response middleware.
        $middleware->api([\Elephant\Response\FormatResponseBodyAdvice::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // This exception is thrown when access is made without a token, but it cannot be caught in the custom middleware; it can only be caught here and a JSON response is thrown.
        $exceptions->renderable(function (AuthenticationException $e) {
            return new JsonResponse(
                app(ThrowableReport::class)->report($e),
            );
        });
    })->create();
```
## Exclude middleware
```php
Route::get('tests',[TestController::class,'test'])->withoutMiddleware(\Elephant\Response\FormatResponseBodyAdvice::class);
```
