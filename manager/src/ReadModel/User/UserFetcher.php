<?php

namespace App\ReadModel\User;

use Doctrine\DBAL\Connection;

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
                ->where('reset_token = :token')
                ->setParameter(':token', $token)
                ->execute()
                ->fetchColumn(0) > 0;
    }
}