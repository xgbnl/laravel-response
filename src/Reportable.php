<?php

namespace Elephant\Response;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface Reportable
{
    public function report(Response|Throwable $response): array;
}