<?php
declare(strict_types=1);

function prices(): array
{
    $variant = (require __DIR__ . '/config.php')['variant'];
    return match ($variant) {
        'A' => ['Кабель', 12500, 'Чехол', 7500],
        'B' => ['Тетрадь', 8000, 'Блокнот', 4000],
        'C' => ['Ручка', 3000, 'Карандаш', 1500],
        default => throw new InvalidArgumentException('Вариант должен быть A/B/C'),
    };
}

function quantity(mixed $raw): int
{
    if (!is_string($raw) || preg_match('/^(0|[1-9][0-9]?)$/D', $raw) !== 1) {
        throw new InvalidArgumentException('Количество: целое от 0 до 99');
    }
    return (int) $raw;
}

function getQuantities(array $query): array
{
    $rawFirst = $query['qty_first'] ?? null;
    $rawSecond = $query['qty_second'] ?? null;

    $first = quantity($rawFirst);
    $second = quantity($rawSecond);

    return [$first, $second];
}

function postQuantities(array $post): array
{
    $rawFirst = $post['qty_first'] ?? null;
    $rawSecond = $post['qty_second'] ?? null;

    $first = quantity($rawFirst);
    $second = quantity($rawSecond);

    return [$first, $second];
}

function totalMinor(int $first, int $second): int
{
    $pricesData = prices();
    
    $priceFirst = $pricesData[1];
    $priceSecond = $pricesData[3];

    return ($first * $priceFirst) + ($second * $priceSecond);
}

function practiceHeader(): void
{
    header('X-Practice: 01');
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function page(string $title, string $body): void
{
    header('Content-Type: text/html; charset=UTF-8');
    practiceHeader();
    if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
        return;
    }
    echo '<!doctype html><html lang="ru"><meta charset="utf-8"><link rel="stylesheet" href="/style.css">';
    echo '<title>' . h($title) . '</title><h1>' . h($title) . '</h1>' . $body . '</html>';
}
