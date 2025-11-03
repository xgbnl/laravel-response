<?php

declare(strict_types=1);

namespace Elephant\Response\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
readonly class CustomResponse
{

    protected ?string $message;

    protected ?int $status;

    public function __construct(?int $status = null, ?string $message = null)
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function hasMessage(): bool
    {
        return !is_null($this->message);
    }

    public function hasStatusCode(): bool
    {
        return !is_null($this->status);
    }
}
