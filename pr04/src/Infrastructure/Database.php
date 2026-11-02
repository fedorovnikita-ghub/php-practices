<?php
declare(strict_types=1);

namespace Course\Infrastructure;

use Course\Http\HttpError;

final class Database
{
    public static function open(array $config): \PDO
    {
        $dsn = $config['dsn'] ?? null;
        if (!is_string($dsn) || !str_starts_with($dsn, 'mysql:')) {
            throw new \InvalidArgumentException('config.local.php: ожидается DSN mysql:');
        }
        if (!in_array('mysql', \PDO::getAvailableDrivers(), true)) {
            throw new HttpError(503, 'driver_missing', 'Не подключено расширение pdo_mysql');
        }
        try {
            return new \PDO(
                $dsn,
                $config['user'] ?? '',
                $config['password'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $error) {
            error_log('Database connection failed: ' . $error->getMessage());
            throw new HttpError(503, 'database_unavailable', 'База данных недоступна; проверьте MySQL и config.local.php');
        }
    }
}