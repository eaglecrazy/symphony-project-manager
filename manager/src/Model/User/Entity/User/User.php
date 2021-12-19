<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;

class User
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $passwordHash;

    public function __construct(int $id, string $email, string $hash)
    {
        $this->id           = $id;
        $this->email        = $email;
        $this->passwordHash = $hash;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
