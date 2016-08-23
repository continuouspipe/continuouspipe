<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160823192554 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE INDEX idx_event_dto_by_tide ON event_dto (tide_uuid(255))');
        $this->addSql('CREATE INDEX idx_event_dto_by_tide_and_event_class ON event_dto (tide_uuid(255), event_class(255))');
        $this->addSql('CREATE INDEX idx_flow_dto_by_team ON flow_dto (team_slug(255))');
        $this->addSql('CREATE INDEX idx_tide_dto_by_flow ON tide_dto (flow_uuid(255))');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP INDEX idx_event_dto_by_tide');
        $this->addSql('DROP INDEX idx_event_dto_by_tide_and_event_class');
        $this->addSql('DROP INDEX idx_flow_dto_by_team');
        $this->addSql('DROP INDEX idx_tide_dto_by_flow');
    }
}
