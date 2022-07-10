<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Email\Confirm;

use App\Model\Flusher;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\UserRepository;

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
        $id = new Id($command->id);

        $user = $this->users->get($id);

        $user->confirmEmailChanging($command->token);

        $this->flusher->flush();
    }
}
