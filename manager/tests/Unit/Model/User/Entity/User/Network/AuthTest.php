<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\Network;

use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Network;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\User\UserBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testSuccess(): void
    {
        $id       = Id::next();
        $date     = new DateTimeImmutable();
        $network  = 'vk';
        $identity = '01';

        $user = User::signUpByNetwork(
            $id,
            $date,
            $network,
            $identity
        );

        self::assertTrue($user->isActive());

        self::assertEquals($id, $user->getId());
        self::assertEquals($date, $user->getDate());

        $networks = $user->getNetworks();
        self::assertCount(1, $networks);

        $first = reset($networks);

        self::assertInstanceOf(Network::class, $first);

        self::assertEquals($network, $first->getNetwork());
        self::assertEquals($identity, $first->getIdentity());

        self::assertTrue($user->getRole()->isUser());
    }
}
