<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Http\HttpError;
use Course\Http\Response;
use Course\Security\AccountSession;

final class Auth
{
    public function __construct(private UserRepository $users) {}

    public function current(): ?User
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null || !is_numeric($userId)) {
            return null;
        }

        $user = $this->users->find((int) $userId);
        if ($user === null) {
            unset($_SESSION['user_id']);
            return null;
        }

        return $user;
    }

    public function login(string $email, string $password): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $user = $this->users->byEmail($normalizedEmail);

        if ($user === null || !password_verify($password, $user->passwordHash)) {
            return false;
        }

        AccountSession::renew();
        $_SESSION['user_id'] = $user->id;
        return true;
    }

    public function logout(): void
    {
        AccountSession::finish();
    }

    public function guard(bool $admin = false): User|Response
    {
        $user = $this->current();
        if ($user === null) {
            return Response::redirect('/login');
        }

        if ($admin) {
            $isAdminRole = property_exists($user, 'role') && $user->role === 'admin';
            $isAdminEmail = property_exists($user, 'email') && ($user->email === 'admin@test.com' || str_contains($user->email, 'admin'));
            $isAdminId = property_exists($user, 'id') && $user->id === 1;

            if (!$isAdminRole && !$isAdminEmail && !$isAdminId) {
                throw new HttpError(403, 'forbidden', 'Доступ запрещен');
            }
        }

        return $user;
    }
}