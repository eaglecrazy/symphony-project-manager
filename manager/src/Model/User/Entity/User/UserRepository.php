<?php

namespace App\Model\User\Entity\User;

interface UserRepository
{
    public function getByEmail(Email $email): User;

    public function hasByEmail(Email $email): bool;

    public function add(User $user): void;

    public function findByConfirmToken(string $token): ?User;

    public function hasByNetworkIdentity(string $network, string $identity);

    public function findByResetToken(string $token): ?User;
}