<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219161950 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE flows_pipelines (flow_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', pipeline_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_45C44FD96F958FA1 (flow_uuid), UNIQUE INDEX UNIQ_45C44FD975AC4689 (pipeline_uuid), PRIMARY KEY(flow_uuid, pipeline_uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flat_pipeline (uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE flows_pipelines ADD CONSTRAINT FK_45C44FD96F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flat_flow (uuid)');
        $this->addSql('ALTER TABLE flows_pipelines ADD CONSTRAINT FK_45C44FD975AC4689 FOREIGN KEY (pipeline_uuid) REFERENCES flat_pipeline (uuid)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE flows_pipelines DROP FOREIGN KEY FK_45C44FD975AC4689');
        $this->addSql('DROP TABLE flows_pipelines');
        $this->addSql('DROP TABLE flat_pipeline');
    }
}
