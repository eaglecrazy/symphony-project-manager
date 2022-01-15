<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;

use DateTimeImmutable;

class User
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $passwordHash;

    public function __construct(string $id, DateTimeImmutable $date, string $email, string $hash)
    {
        $this->id           = $id;
        $this->date         = $date;
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

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }
}
