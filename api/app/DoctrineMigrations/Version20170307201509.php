<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170307201509 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tide_dto DROP FOREIGN KEY FK_DB4F407775AC4689');
        $this->addSql('ALTER TABLE tide_dto ADD CONSTRAINT FK_DB4F407775AC4689 FOREIGN KEY (pipeline_uuid) REFERENCES flat_pipeline (uuid) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tide_dto DROP FOREIGN KEY FK_DB4F407775AC4689');
        $this->addSql('ALTER TABLE tide_dto ADD CONSTRAINT FK_DB4F407775AC4689 FOREIGN KEY (pipeline_uuid) REFERENCES flat_pipeline (uuid)');
    }
}
