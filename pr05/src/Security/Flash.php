<?php
declare(strict_types=1);
namespace Course\Security;

/** Готовый пример для шага 0. */
final class Flash
{
    public static function put(string $message): void
    {
        $_SESSION['flash'] = $message; // 1. Сохранение до ответа 303.
    }
    public static function take(): ?string
    {
        $message = $_SESSION['flash'] ?? null; // 2. Чтение в следующем GET.
        unset($_SESSION['flash']);             // 3. Удаление до показа.
        return is_string($message) ? $message : null;
    }
}
