<?php
declare(strict_types=1);
namespace Course\Api;
use Course\Http\{Router, Request, Response, HttpError};
final class Application
{
    public function __construct(private Router $router) {}
    public function handle(Request $request): Response
    {
        $id = bin2hex(random_bytes(8));
        try { $response = $this->router->dispatch($request); }
        catch (HttpError $error) { $response = Json::problem($error, $id); }
        catch (\Throwable $error) {
            $pending = str_starts_with($error->getMessage(), 'TODO P06-');
            if (!$pending) error_log((string)$error);
            $response = Json::problem(new HttpError($pending ? 501 : 500, $pending ? 'not_implemented' : 'internal_error',
                $pending ? $error->getMessage() : 'Внутренняя ошибка приложения'), $id);
        }
        return $response->withHeader('X-Request-Id',$id)->withHeader('Cache-Control','no-store');
    }
}
