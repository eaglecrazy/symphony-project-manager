<?php

namespace App\ReadModel\User;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class UserFetcher
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function existByResetToken(string $token): bool
    {
        return $this->connection->createQueryBuilder()
                ->select('COUNT (*)')
                ->from('user_users')
                ->where('reset_token_token = :token')
                ->setParameter(':token', $token)
                ->execute()
                ->fetchColumn(0) > 0;
    }

    public function findForAuth(string $email): ?AuthView
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'email',
                'password_hash',
                'role',
                'status'
            )
            ->from('user_users')
            ->where('email = :email')
            ->setParameter(':email', $email)
            ->execute();

        $stmt->setFetchMode(FetchMode::CUSTOM_OBJECT, AuthView::class);

        $result = $stmt->fetch();

        return $result ?: null;
    }
}