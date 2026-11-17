<?php
declare(strict_types=1);
namespace Course\Security;

/** Готовый вспомогательный код. Session::start() вызывается до вывода HTML. */
final class AccountSession
{
    public static function renew(): void
    {
        if (!session_regenerate_id(true)) throw new \RuntimeException('Session renewal');
        $_SESSION = []; // После смены личности старые данные не переносятся.
    }
    public static function finish(): void
    {
        $_SESSION = [];
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'],
            'domain' => $p['domain'], 'secure' => $p['secure'], 'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax']);
        if (!session_destroy()) throw new \RuntimeException('Session finish');
    }
}
