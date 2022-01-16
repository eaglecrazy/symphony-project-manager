<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\SIgnUp;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ConfirmTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = $this->buildSignedUser();

        $user->confirmSignUp();

        self::assertFalse($user->isWait());
        self::assertTrue($user->isActive());

        self::assertNull($user->getConfirmToken());
    }

    public function testAlready(): void
    {
        $user = $this->buildSignedUser();

        $user->confirmSignUp();

        $this->expectExceptionMessage('Пользователь уже подтверждён.');

        $user->confirmSignUp();
    }

    private function buildSignedUser(): User
    {
        return new User(
            Id::next(),
            new DateTimeImmutable(),
            new Email('test@app.test'),
            'hash',
            'token'
        );
    }
}
