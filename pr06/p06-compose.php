<?php
declare(strict_types=1);
use Course\Api\{Application, Tokens, Identity, Login, Catalog};
use Course\Accounts\{UserRepository, AdminItems};
use Course\Repository\PdoItemRepository;
use Course\Http\Router;
return static function(array $variant, PDO $pdo, string $key): Application {
    $tokens = new Tokens($key);
    $users = new UserRepository($pdo);
    $catalog = new Catalog(new PdoItemRepository($pdo), new AdminItems($pdo), new Identity($users,$tokens),$variant['categories']);
    $router = new Router();
    (require __DIR__.'/p06-routes.php')($router,$catalog,new Login($users,$tokens));
    return new Application($router);
};
