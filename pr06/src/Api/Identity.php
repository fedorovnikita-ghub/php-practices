<?php
declare(strict_types=1);

namespace Course\Api;

use Course\Accounts\{User, UserRepository};
use Course\Http\{Request, HttpError};

final class Identity
{
    public function __construct(private UserRepository $users, private Tokens $tokens) {}

    public function require(Request $request, bool $admin = false): User
    {
        $header = $request->header('Authorization');
        if ($header === null || trim($header) === '') {
            throw new HttpError(401, 'unauthorized', 'Требуется токен доступа', [], ['WWW-Authenticate' => 'Bearer']);
        }

        if (preg_match('/^Bearer\s+(\S+)$/i', trim($header), $matches) !== 1) {
            throw new HttpError(401, 'invalid_token', 'Некорректный заголовок Authorization', [], ['WWW-Authenticate' => 'Bearer error="invalid_token"']);
        }

        $userId = $this->tokens->subject($matches[1]);
        $user = $this->users->find($userId);

        if ($user === null) {
            throw new HttpError(401, 'invalid_token', 'Пользователь не найден', [], ['WWW-Authenticate' => 'Bearer error="invalid_token"']);
        }

        if ($admin && $user->role !== 'admin') {
            throw new HttpError(403, 'forbidden', 'Недостаточно прав');
        }

        return $user;
    }
}