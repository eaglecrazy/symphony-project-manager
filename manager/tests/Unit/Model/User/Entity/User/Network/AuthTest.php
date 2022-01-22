<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\Network;

use App\Model\User\Entity\User\Network;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testSuccess(): void
    {
        $network  = 'vk';
        $identity = '01';

        $user = (new UserBuilder())->viaNetwork($network, $identity)->build();

        self::assertTrue($user->isActive());

        $networks = $user->getNetworks();
        self::assertCount(1, $networks);

        $first = reset($networks);

        self::assertInstanceOf(Network::class, $first);

        self::assertEquals($network, $first->getNetwork());
        self::assertEquals($identity, $first->getIdentity());
    }

    public function testAlready(): void
    {
        $network  = 'vk';
        $identity = '01';

        $user = (new UserBuilder())->viaNetwork($network, $identity)->build();

        $this->expectExceptionMessage('Пользователь уже вошёл в систему.');

        $user->signUpByNetwork($network, $identity);
    }
}
