<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\Network;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Network;
use App\Model\User\Entity\User\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AuthTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new User(Id::next(), new DateTimeImmutable());

        $network  = 'vk';
        $identity = '01';

        $user->signUpByNetwork($network, $identity);

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
        $user = new User(Id::next(), new DateTimeImmutable());

        $network  = 'vk';
        $identity = '01';

        $user->signUpByNetwork($network, $identity);

        $this->expectExceptionMessage('Пользователь уже вошёл в систему.');

        $user->signUpByNetwork($network, $identity);
    }
}
