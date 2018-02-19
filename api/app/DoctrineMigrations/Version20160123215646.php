<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160123215646 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_dto (id INT AUTO_INCREMENT NOT NULL, tide_uuid VARCHAR(255) NOT NULL, event_class VARCHAR(255) NOT NULL, serialized_event LONGTEXT NOT NULL, event_datetime TIMESTAMP NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flow_dto (uuid VARCHAR(255) NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:b64Object)\', user_username VARCHAR(255) NOT NULL, team_slug VARCHAR(255) DEFAULT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tide_dto (uuid VARCHAR(255) NOT NULL, flow_uuid VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, log_id VARCHAR(255) NOT NULL, creation_date TIMESTAMP NOT NULL, start_date TIMESTAMP NULL, finish_date TIMESTAMP NULL, configuration LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:b64Object)\', serialized_user LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:b64Object)\', serialized_team LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:b64Object)\', code_reference_sha1 VARCHAR(255) NOT NULL, code_reference_branch VARCHAR(255) NOT NULL, code_reference_code_repository LONGTEXT NOT NULL COMMENT \'(DC2Type:b64Object)\', INDEX IDX_DB4F40776F958FA1 (flow_uuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tide_dto ADD CONSTRAINT FK_DB4F40776F958FA1 FOREIGN KEY (flow_uuid) REFERENCES flow_dto (uuid) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tide_dto DROP FOREIGN KEY FK_DB4F40776F958FA1');
        $this->addSql('DROP TABLE event_dto');
        $this->addSql('DROP TABLE flow_dto');
        $this->addSql('DROP TABLE tide_dto');
    }
}
