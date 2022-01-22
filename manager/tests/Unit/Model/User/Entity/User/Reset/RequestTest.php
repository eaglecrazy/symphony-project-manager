<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\Reset;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Network;
use App\Model\User\Entity\User\ResetToken;
use App\Model\User\Entity\User\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class RequestTest extends TestCase
{
    public function testSuccess(): void
    {
        $now   = new DateTimeImmutable();
        $token = new ResetToken('token', $now->modify('+1 day'));

        $user = $this->buildSignedUpByEmailUser();

        $user->requestPasswordReset($token, $now);

        self::assertNotNull($user->getResetToken());
    }

    public function testAlready(): void
    {
        $now   = new DateTimeImmutable();
        $token = new ResetToken('token', $now->modify('+1 day'));

        $user = $this->buildSignedUpByEmailUser();

        $user->requestPasswordReset($token, $now);

        $this->expectExceptionMessage('Срок действия предыдущего токена ещё не истёк.');

        $user->requestPasswordReset($token, $now);
    }

    public function testExpired(): void
    {
        $now = new DateTimeImmutable();

        $user = $this->buildSignedUpByEmailUser();

        $token1 = new ResetToken('token', $now->modify('+1 day'));
        $user->requestPasswordReset($token1, $now);
        self::assertEquals($token1, $user->getResetToken());

        $token2 = new ResetToken('token', $now->modify('+3 day'));
        $user->requestPasswordReset($token2, $now->modify('+2 day'));
        self::assertEquals($token2, $user->getResetToken());
    }

    public function testWithoutEmail(): void
    {
        $now   = new DateTimeImmutable();
        $token = new ResetToken('token', $now->modify('+1 day'));

        $user = $this->buildUser();

        $this->expectExceptionMessage('У пользователя не указан email.');

        $user->requestPasswordReset($token, $now);
    }

    private function buildSignedUpByEmailUser(): User
    {
        $user = $this->buildUser();

        $user->signUpByEmail(new Email('test@app.test'), 'hash', 'token');

        return $user;
    }

    private function buildUser(): User
    {
        return new User(Id::next(), new DateTimeImmutable());
    }
}