<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\SIgnUp;

use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new User(
            $id = 25,
            $email = 'test@app.test',
            $hash = 'hash'
        );

        self::assertEquals($id, $user->getId());
        self::assertEquals($email, $user->getEmail());
        self::assertEquals($hash, $user->getPasswordHash());
    }
}
