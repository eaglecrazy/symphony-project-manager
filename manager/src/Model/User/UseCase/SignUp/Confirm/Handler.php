<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Confirm;

use App\Model\Flusher;
use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\Service\ConfirmTokenizer;
use App\Model\User\Service\ConfirmTokenSender;
use App\Model\User\Service\PasswordHasher;
use DateTimeImmutable;
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
            throw new DomainException('Некорректный токен');
        }

        $user->confirmSignUp();

        $this->flusher->flush();
    }
}
