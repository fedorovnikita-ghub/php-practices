<?php
declare(strict_types=1);

namespace Course\Api;

use Course\Accounts\UserRepository;
use Course\Http\{Request, Response, HttpError};

final class Login
{
    public function __construct(private UserRepository $users, private Tokens $tokens) {}

    public function __invoke(Request $request): Response
    {
        $data = Json::object($request);

        $email = is_string($data['email'] ?? null) ? strtolower(trim($data['email'])) : '';
        $password = is_string($data['password'] ?? null) ? $data['password'] : '';

        $errors = [];
        if ($email === '') {
            $errors['email'] = 'Укажите email';
        }
        if ($password === '') {
            $errors['password'] = 'Укажите пароль';
        }
        if ($errors !== []) {
            throw new HttpError(422, 'validation_error', 'Проверьте поля входа', $errors);
        }

        $user = $this->users->byEmail($email);
        if ($user === null || !password_verify($password, $user->passwordHash)) {
            throw new HttpError(401, 'invalid_credentials', 'Неверный email или пароль');
        }

        return Json::response([
            'access_token' => $this->tokens->issue($user->id),
            'token_type' => 'Bearer',
            'expires_in' => 900,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }
}