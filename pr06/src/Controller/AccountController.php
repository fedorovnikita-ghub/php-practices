<?php
declare(strict_types=1);
namespace Course\Controller;

use Course\Accounts\Auth;
use Course\Accounts\Registration;
use Course\Accounts\UserRepository;
use Course\Accounts\Templates;
use Course\Http\HttpError;
use Course\Http\Request;
use Course\Http\Response;
use Course\Security\Csrf;
use Course\Security\Flash;

final class AccountController
{
    public function __construct(private UserRepository $users, private Auth $auth, private Templates $ui) {}
    public function form(bool $register): Response
    {
        return Response::html($this->ui->account($register));
    }
    private function csrf(Request $r): array
    {
        $input = $r->form;
        if (!Csrf::valid($input['_csrf'] ?? null)) throw new HttpError(403, 'csrf_failed', 'Некорректный CSRF-токен');
        unset($input['_csrf']);
        return $input;
    }
    public function register(Request $r): Response
    {
        $input = $this->csrf($r);
        try {
            $data = Registration::validate($input);
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->users->create($data['name'], $data['email'], $hash);
            Flash::put('Учётная запись создана. Войдите в каталог.');
            return Response::redirect('/login');
        } catch (HttpError $e) {
            if ($e->status !== 422) throw $e;
            return Response::html($this->ui->account(true, $input, $e->fields), 422);
        }
    }
    public function login(Request $r): Response
    {
        $input = $this->csrf($r);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        if (!is_string($email) || !is_string($password) || strlen($email) > 254 || strlen($password) > 72 ||
            !$this->auth->login($email, $password)) {
            return Response::html($this->ui->account(false, $input, ['login' => 'Неверный email или пароль']), 422);
        }
        Flash::put('Вход выполнен.');
        return Response::redirect('/items');
    }
    public function logout(Request $r): Response
    {
        $this->csrf($r);
        $this->auth->logout();
        return Response::redirect('/login');
    }
}
