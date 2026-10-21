<?php
declare(strict_types=1);

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new RuntimeException('Сначала выполните composer install в корне проекта.');
}
require_once $autoload;
if (!class_exists(\App\Lesson::class) || !class_exists(\App\LessonRequest::class)) {
    throw new RuntimeException('TODO P03-AUTO: настройте App\\ → src/ и выполните composer dump-autoload.');
}
$config = require __DIR__ . '/config.php';
$variants = require __DIR__ . '/data/variants.php';
$variant = $config['variant'] ?? '';
if (!isset($variants[$variant])) {
    throw new InvalidArgumentException('В config.php нужен вариант A, B или C.');
}
$data = $variants[$variant];
$lessons = array_map(static fn(array $row): \App\Lesson => new \App\Lesson(
    $row['topic'], $row['title'], $row['date'], $row['capacity'], $row['occupied']
), $data['lessons']);
return ['variant' => $variant, 'lessons' => $lessons];
