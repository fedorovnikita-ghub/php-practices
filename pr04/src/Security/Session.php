<?php
declare(strict_types=1);
namespace Course\Security;

final class Session
{
    public static function start(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) throw new \RuntimeException('Session directory');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_save_path($directory);
        session_name('PHP_COURSE');
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
        if (!session_start()) throw new \RuntimeException('Session start');
    }
}
