<?php

namespace App\Model\User\Service;

use Ramsey\Uuid\Uuid;

class SignupConfirmTokenizer
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
