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

        return self::identityByUser($user, $username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserIdentity) {
            throw new UnsupportedUserException('Invalid user class ' . get_class($user));
        }

        $username = $user->getUsername();

        $user = $this->loadUser($username);

        return self::identityByUser($user, $username);
    }

    public function supportsClass($class)
    {
        return $class instanceof UserIdentity;
    }

    private static function identityByUser(AuthView $user, string $username): UserIdentity
    {
        return new UserIdentity(
            $user->id,
            $username,
            $user->password_hash ?: '',
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
        $chunks = explode(':', $username);

        if (count($chunks) === 2) {
            $user = $this->users->findForAuthByNetwork($chunks[0], $chunks[1]);

            if ($user) {
                return $user;
            }
        }

        $user = $this->users->findForAuthByEmail($username);

        if (!$user) {
            throw new UsernameNotFoundException('');
        }

        return $user;
    }
}