<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Network\Detach;

class Command
{
    /**
     * @var string
     */
    public $network;

    /**
     * @var string
     */
    public $identity;

    /**
     * @var string
     */
    public $user;

    public function __construct(string $user, string $network, string $identity)
    {
        $this->network  = $network;
        $this->identity = $identity;
        $this->user     = $user;
    }
}
