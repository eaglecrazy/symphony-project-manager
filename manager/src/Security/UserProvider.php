<?php

declare(strict_types=1);

namespace App\Security;

use App\ReadModel\User\AuthView;
use App\ReadModel\User\UserFetcher;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var UserFetcher
     */
    private $users;

    public function __construct(UserFetcher $users)
    {
        $this->users = $users;
    }

    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->loadUser($username);

        return self::identityByUser($user);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserIdentity) {
            throw new UnsupportedUserException('Invalid user class ' . get_class($user));
        }

        $user = $this->loadUser($user->getUsername());

        return self::identityByUser($user);
    }

    public function supportsClass($class)
    {
        return $class instanceof UserIdentity;
    }

    private static function identityByUser(AuthView $user): UserIdentity
    {
        return new UserIdentity(
            $user->id,
            $user->email,
            $user->password_hash,
            $user->role,
            $user->status
        );
    }

    /**
     * @param string $username
     * @return AuthView
     */
    private function loadUser(string $username): AuthView
    {
        $user = $this->users->findForAuth($username);

        if (!$user) {
            throw new UsernameNotFoundException('');
        }

        return $user;
    }
}