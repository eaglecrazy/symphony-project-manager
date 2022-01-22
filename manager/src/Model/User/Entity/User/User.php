<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use DomainException;

class User
{
    private const STATUS_NEW    = 'new';
    private const STATUS_WAIT   = 'wait';
    private const STATUS_ACTIVE = 'active';

    /** @var Id */
    private $id;

    /** @var DateTimeImmutable */
    private $date;

    /** @var Email */
    private $email;

    /** @var string */
    private $passwordHash;

    /** @var string */
    private $confirmToken;

    /** @var string */
    private $status;

    /** @var Network[]|ArrayCollection */
    private $networks;

    /** @var ResetToken */
    private $resetToken;

    public function __construct(Id $id, DateTimeImmutable $date)
    {
        $this->id       = $id;
        $this->date     = $date;
        $this->status   = self::STATUS_NEW;
        $this->networks = new ArrayCollection();
    }

    /**
     * @param Email $email
     * @param string $hash
     * @param string $token
     */
    public function signUpByEmail(Email $email, string $hash, string $token)
    {
        if (!$this->isNew()) {
            throw new DomainException('Пользователь уже вошёл в систему.');
        }

        $this->email        = $email;
        $this->passwordHash = $hash;
        $this->confirmToken = $token;
        $this->status       = self::STATUS_WAIT;
    }

    public function signUpByNetwork(string $network, string $identity)
    {
        if (!$this->isNew()) {
            throw new DomainException('Пользователь уже вошёл в систему.');
        }

        $this->attachNetwork($network, $identity);
        $this->status = self::STATUS_ACTIVE;
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

    /**
     * @return string
     */
    public function getConfirmToken(): ?string
    {
        return $this->confirmToken;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * @return bool
     */
    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     *
     */
    public function confirmSignUp(): void
    {
        if (!$this->isWait()) {
            throw new DomainException('Пользователь уже подтверждён.');
        }

        $this->status       = self::STATUS_ACTIVE;
        $this->confirmToken = null;
    }

    /**
     * @param string $network
     * @param string $identity
     */
    private function attachNetwork(string $network, string $identity): void
    {
        foreach ($this->networks as $existing) {
            if ($existing->isForNetwork($network)) {
                throw new DomainException('Социальная сеть уже назначена.');
            }
        }

        $this->networks->add(new Network($this, $network, $identity));
    }

    /**
     * @return Network[]
     */
    public function getNetworks(): array
    {
        return $this->networks->toArray();
    }

    /**
     * @param ResetToken $token
     * @param DateTimeImmutable $date
     */
    public function requestPasswordReset(ResetToken $token, DateTimeImmutable $date): void
    {
        if (!$this->email) {
            throw new DomainException('У пользователя не указан email.');
        }

        if ($this->resetToken && !$this->resetToken->isExpiredTo($date)) {
            throw new DomainException('Срок действия предыдущего токена ещё не истёк.');
        }

        $this->resetToken = $token;
    }

    /**
     * @return ResetToken
     */
    public function getResetToken(): ResetToken
    {
        return $this->resetToken;
    }
}
