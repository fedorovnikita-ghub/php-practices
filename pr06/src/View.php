<?php
declare(strict_types=1);
namespace Course;

final class View
{
    public static function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public static function page(string $title, string $body): string
    {
        return '<!doctype html><html lang="ru"><meta charset="utf-8"><meta name="viewport" content="width=device-width">'
            . '<title>' . self::h($title) . '</title><link rel="stylesheet" href="/style.css">'
            . '<nav><a href="/items">Каталог</a><a href="/health.php">Среда</a></nav><h1>' . self::h($title) . '</h1>' . $body . '</html>';
    }
}
