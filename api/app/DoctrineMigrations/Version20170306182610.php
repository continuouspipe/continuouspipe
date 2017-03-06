<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170306182610 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tide_dto ADD pipeline_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tide_dto ADD CONSTRAINT FK_DB4F407775AC4689 FOREIGN KEY (pipeline_uuid) REFERENCES flat_pipeline (uuid)');
        $this->addSql('CREATE INDEX IDX_DB4F407775AC4689 ON tide_dto (pipeline_uuid)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tide_dto DROP FOREIGN KEY FK_DB4F407775AC4689');
        $this->addSql('DROP INDEX IDX_DB4F407775AC4689 ON tide_dto');
        $this->addSql('ALTER TABLE tide_dto DROP pipeline_uuid');
    }
}
