<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Network\Auth;

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

    public function __construct(UserRepository $users, Flusher $flusher)
    {
        $this->users   = $users;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($this->users->hasByNetworkIdentity($command->network, $command->identity)) {
            throw new DomainException('Пользователь уже существует');
        }

        $user = new User(
            Id::next(),
            new DateTimeImmutable()
        );

        $user->signUpByNetwork($command->network, $command->identity);

        $this->users->add($user);

        $this->flusher->flush();
    }
}
