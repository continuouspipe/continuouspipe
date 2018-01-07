<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170110155245 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE flat_pipeline DROP FOREIGN KEY FK_57F786856F958FA1');
        $this->addSql('ALTER TABLE flat_pipeline ADD CONSTRAINT FK_57F786856F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flat_flow (uuid) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE flat_pipeline DROP FOREIGN KEY FK_57F786856F958FA1');
        $this->addSql('ALTER TABLE flat_pipeline ADD CONSTRAINT FK_57F786856F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flat_flow (uuid)');
    }
}
