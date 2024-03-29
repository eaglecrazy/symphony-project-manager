<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230107190826 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_users ADD name_first VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_users ADD name_last VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE user_users SET name_first = \'\'');
        $this->addSql('UPDATE user_users SET name_last  = \'\'');

        $this->addSql('ALTER TABLE user_users ALTER name_first SET NOT NULL');
        $this->addSql('ALTER TABLE user_users ALTER name_last SET NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_users DROP name_first');
        $this->addSql('ALTER TABLE user_users DROP name_last');
    }
}
