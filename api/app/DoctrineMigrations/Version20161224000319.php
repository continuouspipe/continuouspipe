<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161224000319 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bitbucket_installation (client_key VARCHAR(255) NOT NULL, shared_secret VARCHAR(255) NOT NULL, installation_key VARCHAR(255) NOT NULL, public_key VARCHAR(255) NOT NULL, base_url VARCHAR(255) NOT NULL, base_api_url VARCHAR(255) NOT NULL, consumer_id INT NOT NULL, consumer_name VARCHAR(255) NOT NULL, consumer_key VARCHAR(255) NOT NULL, consumer_secret VARCHAR(255) NOT NULL, principal_uuid VARCHAR(255) NOT NULL, principal_username VARCHAR(255) NOT NULL, principal_type VARCHAR(255) NOT NULL, user_uuid VARCHAR(255) NOT NULL, user_username VARCHAR(255) NOT NULL, user_type VARCHAR(255) NOT NULL, PRIMARY KEY(client_key)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE bitbucket_installation');
    }
}
