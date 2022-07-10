<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Email\Request;

use App\Model\Flusher;
use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\Service\NewEmailConfirmTokenizer;
use App\Model\User\Service\NewEmailConfirmTokenSender;
use DomainException;

class Handler
{
    /** @var UserRepository */
    private $users;

    /** @var NewEmailConfirmTokenizer */
    private $tokenizer;

    /** @var NewEmailConfirmTokenSender */
    private $sender;

    /** @var Flusher */
    private $flusher;

    public function __construct(
        UserRepository $users,
        NewEmailConfirmTokenizer $tokenizer,
        NewEmailConfirmTokenSender $sender,
        Flusher $flusher
    ) {
        $this->users     = $users;
        $this->flusher   = $flusher;
        $this->tokenizer = $tokenizer;
        $this->sender    = $sender;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get(new Id($command->id));

        $email = new Email($command->email);

        if ($this->users->hasByEmail($email)) {
            throw new DomainException('Email already in use');
        }

        $token = $this->tokenizer->generate();

        $user->requestEmailChanging($email, $token);

        $this->flusher->flush();

        $this->sender->send($email, $token);
    }
}
