<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161213203544 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tide_dto DROP FOREIGN KEY FK_DB4F40776F958FA1');
        $this->addSql('DROP INDEX IDX_DB4F40776F958FA1 ON tide_dto');
        $this->addSql('ALTER TABLE tide_dto CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE flow_uuid flow_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tide_dto CHANGE uuid uuid VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE flow_uuid flow_uuid VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE tide_dto ADD CONSTRAINT FK_DB4F40776F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flow_dto (uuid) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DB4F40776F958FA1 ON tide_dto (flow_uuid)');
    }
}
