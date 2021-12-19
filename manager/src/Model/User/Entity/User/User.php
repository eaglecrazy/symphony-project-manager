<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;

class User
{
    /**
     * @var string
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

    public function __construct(string $id, string $email, string $hash)
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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
