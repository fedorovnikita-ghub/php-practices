<?php
declare(strict_types=1);

use Course\Accounts\{Auth, UserRepository, AdminItems, Templates};
use Course\Controller\{AccountController, AccountCatalogController};
use Course\Http\{Application, Router};
use Course\Repository\PdoItemRepository;

// Готовая сборка зависимостей. В bootstrap.php используйте этот файл вместо прежней фабрики.
return static function (array $variant, PDO $pdo): Application {
    $router = new Router();
    $users = new UserRepository($pdo);
    $auth = new Auth($users);
    $ui = new Templates($auth, $variant['categories']);
    $accounts = new AccountController($users, $auth, $ui);
    $catalog = new AccountCatalogController(new PdoItemRepository($pdo), new AdminItems($pdo), $auth, $ui, $variant['categories']);
    (require __DIR__ . '/p05-routes.php')($router, $accounts, $catalog);
    return new Application($router);
};
