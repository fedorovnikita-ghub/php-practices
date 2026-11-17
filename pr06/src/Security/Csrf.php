<?php
declare(strict_types=1);
namespace Course\Security;

final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }
    public static function valid(mixed $token): bool
    {
        return is_string($token) && isset($_SESSION['csrf']) && is_string($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $token);
    }
}
