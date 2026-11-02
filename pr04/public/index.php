<?php
declare(strict_types=1);
ini_set('display_errors', '0');
$root = dirname(__DIR__);
try {
    if (!is_file($root . '/vendor/autoload.php')) throw new RuntimeException('Выполните composer install');
    require $root . '/vendor/autoload.php';
    $config = require $root . '/config.php';
    $variant = (require $root . '/data/variants.php')[$config['variant']] ?? throw new InvalidArgumentException('Вариант A/B/C');
    if (!is_file($root . '/config.local.php')) {
        throw new Course\Http\HttpError(503, 'setup_required', 'Скопируйте config.example.php в config.local.php');
    }
    Course\Security\Session::start($root . '/var/sessions');
    $pdo = Course\Infrastructure\Database::open(require $root . '/config.local.php');
    $factory = require $root . '/bootstrap.php';
    $request = Course\Http\Request::fromGlobals();
    $factory($variant, $pdo)->handle($request)->send($request->method);
} catch (Throwable $error) {
    // Пароль, DSN, SQL и текст исключения PDO посетителю не показываются.
    $pending = str_starts_with($error->getMessage(), 'TODO ') || $error->getMessage() === 'Выполните composer install';
    $status = $error instanceof Course\Http\HttpError ? $error->status : ($pending ? 501 : 500);
    $message = ($pending || $error instanceof Course\Http\HttpError) ? $error->getMessage() : 'Ошибка приложения; проверьте журнал';
    if (!$pending && !$error instanceof Course\Http\HttpError) error_log((string) $error);
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        echo '<!doctype html><meta charset="utf-8"><title>Подготовка проекта</title><h1>Операция не выполнена</h1><p>'
            . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }
}
