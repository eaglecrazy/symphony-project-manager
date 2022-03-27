<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Confirm;

class Command
{
    /**
     * @var string
     */
    public $token;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
