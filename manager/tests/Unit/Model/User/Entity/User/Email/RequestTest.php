<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\Email;

use App\Model\User\Entity\User\Email;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = (new UserBuilder())->viaEmail()->confirmed()->build();

        $email = new Email('new@app.test');
        $token = 'token';

        $user->requestEmailChanging($email, $token);

        self::assertEquals($email, $user->getNewEmail());
        self::assertEquals($token, $user->getNewEmailToken());
    }

    public function testSame(): void
    {
        $email = new Email('new@app.test');
        $token = 'token';

        $user = (new UserBuilder())->viaEmail($email)->confirmed()->build();

        $this->expectExceptionMessage('Email is already same');

        $user->requestEmailChanging($email, $token);
    }

    public function testNotConfirmed(): void
    {
        $email = new Email('new@app.test');

        $user = (new UserBuilder())->viaEmail($email)->build();

        $this->expectExceptionMessage('User is not active');

        $user->requestEmailChanging($email, 'token');
    }
}
