<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Request;

use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManager;
use DomainException;
use Ramsey\Uuid\Uuid;

class Handler
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function handle(Command $command)
    {
        $email = mb_strtolower($command->email);

        if ($this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
            throw new DomainException('Пользователь уже существует.');
        }

        $user = new User(
            Uuid::uuid4()->toString(),
            $email,
            password_hash($command->password, PASSWORD_ARGON2I)
        );

        $this->em->persist($user);
        $this->em->flush();
    }
}
