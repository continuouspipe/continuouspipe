<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219170617 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE flows_pipelines');
        $this->addSql('ALTER TABLE flat_pipeline ADD flow_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE flat_pipeline ADD CONSTRAINT FK_57F786856F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flat_flow (uuid)');
        $this->addSql('CREATE INDEX IDX_57F786856F958FA1 ON flat_pipeline (flow_uuid)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE flows_pipelines (flow_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', pipeline_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_45C44FD975AC4689 (pipeline_uuid), INDEX IDX_45C44FD96F958FA1 (flow_uuid), PRIMARY KEY(flow_uuid, pipeline_uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE flows_pipelines ADD CONSTRAINT FK_45C44FD96F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flat_flow (uuid)');
        $this->addSql('ALTER TABLE flows_pipelines ADD CONSTRAINT FK_45C44FD975AC4689 FOREIGN KEY (pipeline_uuid) REFERENCES flat_pipeline (uuid)');
        $this->addSql('ALTER TABLE flat_pipeline DROP FOREIGN KEY FK_57F786856F958FA1');
        $this->addSql('DROP INDEX IDX_57F786856F958FA1 ON flat_pipeline');
        $this->addSql('ALTER TABLE flat_pipeline DROP flow_uuid');
    }
}
