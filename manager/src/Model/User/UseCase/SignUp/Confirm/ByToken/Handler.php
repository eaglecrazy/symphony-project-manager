<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Confirm\ByToken;

use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;
use DomainException;

class Handler
{
    /** @var UserRepository */
    private $users;

    /** @var Flusher */
    private $flusher;

    public function __construct(UserRepository $users, Flusher $flusher) {
        $this->users     = $users;
        $this->flusher   = $flusher;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->findByConfirmToken($command->token);

        if(!$user){
            throw new DomainException('Incorrect or confirmed token.');
        }

        $user->confirmSignUp();

        $this->flusher->flush();
    }
}
