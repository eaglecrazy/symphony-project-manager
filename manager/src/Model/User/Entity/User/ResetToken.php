<?php

namespace App\Model\User\Entity\User;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

class ResetToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var DateTimeImmutable
     */
    private $expires;

    public function __construct(string $token, DateTimeImmutable $expires)
    {
        Assert::notEmpty($token);
        $this->token   = $token;
        $this->expires = $expires;
    }

    /**
     * @param DateTimeImmutable $date
     * @return bool
     */
    public function isExpiredTo(DateTimeImmutable $date): bool
    {
        return $this->expires <= $date;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
