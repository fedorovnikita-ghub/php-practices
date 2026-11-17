<?php
declare(strict_types=1);
namespace Course\Accounts;

final readonly class User
{
    public function __construct(public int $id, public string $name, public string $email,
        public string $passwordHash, public string $role) {}
}
