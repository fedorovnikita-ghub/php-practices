<?php
declare(strict_types=1);

use Course\Http\Request;
use Course\Http\Response;
use Course\Http\Router;
use Course\Controller\AccountController;
use Course\Controller\AccountCatalogController;

return static function (Router $router, AccountController $accounts, AccountCatalogController $catalog): void {
    $router->add('GET', '/', [$catalog, 'listing']);
    $router->add('GET', '/items', [$catalog, 'listing']);
    $router->add('GET', '/items/new', [$catalog, 'newForm']);
    $router->add('GET', '/items/{id}', static function (Request $request, int $id) use ($catalog) {
        return $catalog->show($id);
    });
    $router->add('POST', '/items', [$catalog, 'save']);
    $router->add('GET', '/items/{id}/edit', static function (Request $request, int $id) use ($catalog) {
        return $catalog->editForm($id);
    });
    $router->add('POST', '/items/{id}/edit', [$catalog, 'save']);
    $router->add('POST', '/items/{id}/delete', [$catalog, 'delete']);

    // Маршруты для авторизации и регистрации
    $router->add('GET', '/register', static function (Request $request) use ($accounts) {
        return $accounts->form(true);
    });
    $router->add('GET', '/login', static function (Request $request) use ($accounts) {
        return $accounts->form(false);
    });
    $router->add('POST', '/register', [$accounts, 'register']);
    $router->add('POST', '/login', [$accounts, 'login']);
    $router->add('POST', '/logout', [$accounts, 'logout']);
};