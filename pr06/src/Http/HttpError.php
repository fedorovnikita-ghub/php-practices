<?php
declare(strict_types=1);
namespace Course\Http;

final class HttpError extends \RuntimeException
{
    public function __construct(public readonly int $status, public readonly string $errorCode,
        string $message, public readonly array $fields = [], public readonly array $headers = [])
    {
        parent::__construct($message);
    }
}
