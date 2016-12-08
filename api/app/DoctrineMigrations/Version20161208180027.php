<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161208180027 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE flat_flow (uuid VARCHAR(255) NOT NULL, configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', repository_identifier VARCHAR(255) NOT NULL, team_slug VARCHAR(255) NOT NULL, team_name VARCHAR(255) DEFAULT NULL, team_bucket_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_username VARCHAR(255) NOT NULL, user_email VARCHAR(255) DEFAULT NULL, user_bucket_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE flat_flow');
    }
}
