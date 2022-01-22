<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\Reset;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\ResetToken;
use App\Model\User\Entity\User\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ResetTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = $this->buildSignedUpByEmailUser();

        $now   = new DateTimeImmutable();
        $token = new ResetToken('token', $now->modify('+1 day'));
        $hash  = 'hash';

        $user->requestPasswordReset($token, $now);

        self::assertNotNull($user->getResetToken());

        $user->passwordReset($now, $hash);

        self::assertNull($user->getResetToken());

        self::assertEquals($hash, $user->getPasswordHash());
    }

    public function testExpiredToken(): void
    {
        $user = $this->buildSignedUpByEmailUser();

        $now   = new DateTimeImmutable();
        $token = new ResetToken('token', $now);

        $user->requestPasswordReset($token, $now);

        self::expectExceptionMessage('Токен просрочен.');

        $user->passwordReset($now->modify('+1 day'), 'hash');
    }

    public function testNotRequested(): void
    {
        $user = $this->buildSignedUpByEmailUser();

        $now = new DateTimeImmutable();

        self::expectExceptionMessage('Сброс пароля не был запрошен.');

        $user->passwordReset($now, 'hash');
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