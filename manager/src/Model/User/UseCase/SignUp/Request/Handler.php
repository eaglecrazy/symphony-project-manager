<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Request;

use App\Model\Flusher;
use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\Service\SignupConfirmTokenizer;
use App\Model\User\Service\SignupConfirmTokenSender;
use App\Model\User\Service\PasswordHasher;
use DateTimeImmutable;
use DomainException;

class Handler
{
    /** @var UserRepository */
    private $users;

    /** @var PasswordHasher */
    private $hasher;

    /** @var Flusher */
    private $flusher;

    /** @var SignupConfirmTokenizer */
    private $tokenizer;

    /** @var SignupConfirmTokenSender */
    private $sender;

    public function __construct(
        UserRepository $users,
        PasswordHasher $hasher,
        SignupConfirmTokenizer $tokenizer,
        SignupConfirmTokenSender $sender,
        Flusher $flusher
    ) {
        $this->users     = $users;
        $this->hasher    = $hasher;
        $this->flusher   = $flusher;
        $this->tokenizer = $tokenizer;
        $this->sender    = $sender;
    }

    public function handle(Command $command): void
    {
        $email = new Email($command->email);

        if ($this->users->hasByEmail($email)) {
            throw new DomainException('User already exists.');
        }

        $token = $this->tokenizer->generate();

        $user = User::signUpByEmail(
            Id::next(),
            new DateTimeImmutable(),
            new Name($command->firstName, $command->lastName),
            $email,
            $this->hasher->hash($command->password),
            $token
        );

        $this->users->add($user);

        $this->sender->send($email, $token);

        $this->flusher->flush();
    }
}
