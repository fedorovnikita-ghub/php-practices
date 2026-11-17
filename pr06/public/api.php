<?php
declare(strict_types=1);
ini_set('display_errors','0');
$root = dirname(__DIR__);
$requestId = bin2hex(random_bytes(8));
try {
    require $root.'/vendor/autoload.php';
    $request = Course\Http\Request::fromGlobals();
    $config = require $root.'/config.php';
    $variant = (require $root.'/data/variants.php')[$config['variant']];
    $pdo = Course\Infrastructure\Database::open(require $root.'/config.local.php');
    $key = @file_get_contents($root.'/var/jwt.key');
    if ($key === false) throw new RuntimeException('Run bin/p06-key.php');
    $factory = require $root.'/p06-compose.php';
    $factory($variant,$pdo,$key)->handle($request)->send($request->method);
} catch (Throwable $error) {
    $http = $error instanceof Course\Http\HttpError;
    $pending = str_starts_with($error->getMessage(),'TODO P06-');
    $status = $http ? $error->status : ($pending ? 501 : 500);
    if (!$http && !$pending) error_log((string)$error);
    http_response_code($status);
    header('Content-Type: application/problem+json; charset=UTF-8');
    header('X-Request-Id: '.$requestId);
    header('Cache-Control: no-store');
    if ($http) foreach ($error->headers as $name=>$value) header($name.': '.$value);
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD') echo json_encode(['type'=>'about:blank',
        'title'=>$http || $pending ? $error->getMessage() : 'Ошибка подготовки API; проверьте журнал',
        'status'=>$status,'code'=>$http ? $error->errorCode : ($pending ? 'not_implemented' : 'setup_error'),
        'errors'=>(object)[],'requestId'=>$requestId],JSON_UNESCAPED_UNICODE);
}
