<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150828164527 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE event_dto_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event_dto (id INT NOT NULL, tide_uuid VARCHAR(255) NOT NULL, event_class VARCHAR(255) NOT NULL, serialized_event TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE flow_dto (uuid VARCHAR(255) NOT NULL, context TEXT NOT NULL, tasks TEXT NOT NULL, user_username VARCHAR(255) NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('COMMENT ON COLUMN flow_dto.context IS \'(DC2Type:b64Object)\'');
        $this->addSql('COMMENT ON COLUMN flow_dto.tasks IS \'(DC2Type:b64Object)\'');
        $this->addSql('CREATE TABLE tide_dto (uuid VARCHAR(255) NOT NULL, flow_uuid VARCHAR(255) DEFAULT NULL, user_email VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, log_id VARCHAR(255) NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, finish_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, code_reference_sha1 VARCHAR(255) NOT NULL, code_reference_branch VARCHAR(255) NOT NULL, code_reference_code_repository TEXT NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX IDX_DB4F40776F958FA1 ON tide_dto (flow_uuid)');
        $this->addSql('COMMENT ON COLUMN tide_dto.code_reference_code_repository IS \'(DC2Type:b64Object)\'');
        $this->addSql('ALTER TABLE tide_dto ADD CONSTRAINT FK_DB4F40776F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flow_dto (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tide_dto DROP CONSTRAINT FK_DB4F40776F958FA1');
        $this->addSql('DROP SEQUENCE event_dto_id_seq CASCADE');
        $this->addSql('DROP TABLE event_dto');
        $this->addSql('DROP TABLE flow_dto');
        $this->addSql('DROP TABLE tide_dto');
    }
}
