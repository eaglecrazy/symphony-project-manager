<?php

namespace App\Model\User\Entity\User;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class ResetToken
{
    /**
     * @var string
     * @ORM\Column (type="string", nullable=true)
     */
    private $token;

    /**
     * @var DateTimeImmutable
     * @ORM\Column (type="datetime_immutable", nullable=true)
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

    /**
     * @return bool
     * @internal for postload callback
     */
    public function isEmpty(): bool
    {
        return empty($this->token);
    }
}
