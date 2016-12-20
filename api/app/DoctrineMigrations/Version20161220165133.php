<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161220165133 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX idx_tide_dto_by_flow_sha1_and_branch ON tide_dto (flow_uuid, code_reference_sha1, code_reference_branch)');
        $this->addSql('CREATE INDEX idx_tide_dto_by_flow_and_branch ON tide_dto (flow_uuid, code_reference_branch)');
        $this->addSql('CREATE INDEX idx_tide_dto_by_flow_branch_and_status ON tide_dto (flow_uuid, code_reference_branch, status)');
        $this->addSql('CREATE INDEX idx_tide_dto_by_flow_and_status ON tide_dto (flow_uuid, status)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX idx_tide_dto_by_flow_sha1_and_branch ON tide_dto');
        $this->addSql('DROP INDEX idx_tide_dto_by_flow_and_branch ON tide_dto');
        $this->addSql('DROP INDEX idx_tide_dto_by_flow_branch_and_status ON tide_dto');
        $this->addSql('DROP INDEX idx_tide_dto_by_flow_and_status ON tide_dto');
    }
}
