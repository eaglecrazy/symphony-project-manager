<?php

namespace App\Tests\Builder\User;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use DateTimeImmutable;

class UserBuilder
{
    /** @var Id */
    private $id;

    /** @var DateTimeImmutable */
    private $date;

    /** @var Email */
    private $email;

    /** @var string */
    private $hash;

    /** @var string */
    private $token;

    private $confirmed;

    /** @var string */
    private $network;

    /** @var string */
    private $identity;

    public function __construct()
    {
        $this->id   = Id::next();
        $this->date = new DateTimeImmutable();
    }

    public function viaEmail(Email $email = null, string $hash = null, string $token = null): self
    {
        $clone = clone $this;

        $clone->email = $email ?? new Email('mail@app.test');
        $clone->hash  = $hash ?? 'hash';
        $clone->token = $hash ?? 'token';

        return $clone;
    }

    /**
     * @return UserBuilder
     */
    public function confirmed(): self
    {
        $clone            = clone $this;
        $clone->confirmed = true;
        return $clone;
    }

    public function viaNetwork(string $network = null, string $identity = null): self
    {
        $clone = clone $this;

        $clone->network  = $network ?? 'vk';
        $clone->identity = $identity ?? '0001';

        return $clone;
    }

    public function build(): User
    {
        $user = new User($this->id, $this->date);

        if ($this->email) {
            $user->signUpByEmail($this->email, $this->hash, $this->token);

            if ($this->confirmed) {
                $user->confirmSignUp();
            }
        }

        if ($this->network) {
            $user->signUpByNetwork($this->network, $this->identity);
        }

        return $user;
    }
}
