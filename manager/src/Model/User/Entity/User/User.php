<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;

use DateTimeImmutable;

class User
{
    /** @var Id */
    private $id;

    /** @var DateTimeImmutable */
    private $date;

    /** @var Email */
    private $email;

    /** @var string */
    private $passwordHash;

    public function __construct(Id $id, DateTimeImmutable $date, Email $email, string $hash)
    {
        $this->id           = $id;
        $this->date         = $date;
        $this->email        = $email;
        $this->passwordHash = $hash;
    }

    /**
     * @return Id
     */
    public function getId(): Id
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

    /**
     * @return Email
     */
    public function getEmail(): Email
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
}
