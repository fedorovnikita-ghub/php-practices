<?php
declare(strict_types=1);

namespace Course\Http;

use Course\View;

final class Application
{
    public function __construct(private Router $router) {}

    public function handle(Request $request): Response
    {
        $requestId = bin2hex(random_bytes(8));
        try {
            $response = $this->router->dispatch($request);
        } catch (HttpError $error) {
            $response = new Response($error->status, View::page($error->getMessage(), '<p>' . View::h($error->getMessage()) . '</p>'),
                ['Content-Type' => 'text/html; charset=UTF-8'] + $error->headers);
        } catch (\Throwable $error) {
            $pending = str_starts_with($error->getMessage(), 'TODO ');
            if (!$pending) error_log((string) $error);
            $problem = new HttpError($pending ? 501 : 500, $pending ? 'not_implemented' : 'internal_error',
                $pending ? $error->getMessage() : 'Внутренняя ошибка приложения');
            $response = Response::html(View::page('Операция не выполнена', '<p>' . View::h($problem->getMessage()) . '</p>'), $problem->status);
        }
        return $response->withHeader('X-Request-Id', $requestId);
    }
}