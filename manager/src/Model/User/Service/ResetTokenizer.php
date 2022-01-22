<?php

namespace App\Model\User\Service;

use App\Model\User\Entity\User\ResetToken;
use DateInterval;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class ResetTokenizer
{
    /**
     * @var DateInterval
     */
    private $interval;

    public function __construct(DateInterval $interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return ResetToken
     */
    public function generate(): ResetToken
    {
        $date = new DateTimeImmutable();

        return new ResetToken(Uuid::uuid4()->toString(), $date->add($this->interval));
    }
}
