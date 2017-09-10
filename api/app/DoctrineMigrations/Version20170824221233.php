<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170824221233 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE resource_usage_history (entry_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', flow_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', environment_identifier VARCHAR(255) NOT NULL, date_time DATETIME NOT NULL, resources_usage_requests_cpu VARCHAR(255) NOT NULL, resources_usage_requests_memory VARCHAR(255) NOT NULL, resources_usage_limits_cpu VARCHAR(255) NOT NULL, resources_usage_limits_memory VARCHAR(255) NOT NULL, PRIMARY KEY(entry_uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE resource_usage_history');
    }
}
