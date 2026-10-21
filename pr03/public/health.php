<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
echo 'Практика: 03' . PHP_EOL;
echo 'PHP: ' . PHP_VERSION . PHP_EOL;
echo 'PHP 8.3–8.5: ' . (PHP_VERSION_ID >= 80300 && PHP_VERSION_ID < 80600 ? 'OK' : 'проверьте версию') . PHP_EOL;
echo 'mbstring: ' . (extension_loaded('mbstring') ? 'OK' : 'не найдено') . PHP_EOL;
