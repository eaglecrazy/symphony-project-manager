<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Entity\User\User;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserIdentity implements UserInterface, EquatableInterface
{
    /** @var string */
    private $id;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $role;

    /** @var string */
    private $status;

    /** @var string */
    private $display;

    public function __construct(
        string $id,
        string $username,
        string $password,
        string $display,
        string $role,
        string $status
    ) {
        $this->id       = $id;
        $this->username = $username;
        $this->password = $password;
        $this->role     = $role;
        $this->status   = $status;
        $this->display  = $display;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->status === User::STATUS_ACTIVE;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return
            $this->id === $user->id
            && $this->password === $user->password
            && $this->role === $user->role
            && $this->status === $user->status;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }
}