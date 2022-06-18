<?php

namespace App\Model\User\Entity\User;

use Webmozart\Assert\Assert;

class Role
{
    public const USER  = 'ROLE_USER';
    public const ADMIN = 'ROLE_ADMIN';

    private $name;

    public function __construct(string $name)
    {
        Assert::oneOf($name, [
            self::ADMIN,
            self::USER,
        ]);

        $this->name = $name;
    }

    public static function user(): self
    {
        return new self(self::USER);
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public function isUser(): bool
    {
        return $this->name === self::USER;
    }

    public function isAdmin(): bool
    {
        return $this->name === self::ADMIN;
    }

    public function isEqual(Role $role): bool
    {
        return $this->name === $role->name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
