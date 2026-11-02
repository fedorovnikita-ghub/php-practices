<?php
declare(strict_types=1);

use Course\Controller\ItemController;
use Course\Http\Application;
use Course\Http\Request;
use Course\Http\Response;
use Course\Http\Router;
use Course\Repository\PdoItemRepository;

return static function (array $variant, PDO $pdo): Application {
    $router = new Router();
    $repository = new PdoItemRepository($pdo);
    
    $controller = new ItemController($repository, $variant['categories']);

    $router->add('GET', '/', [$controller, 'listing']);
    $router->add('GET', '/items', [$controller, 'listing']);
    $router->add('GET', '/items/new', [$controller, 'newForm']);
    $router->add('GET', '/items/{id}', [$controller, 'show']);
    $router->add('POST', '/items', [$controller, 'create']);

    return new Application($router);
};