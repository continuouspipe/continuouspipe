<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161222145736 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE abstract_code_repository (identifier VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, organisation VARCHAR(255) DEFAULT NULL, default_branch VARCHAR(255) DEFAULT NULL, private TINYINT(1) DEFAULT NULL, owner VARCHAR(255) DEFAULT NULL, PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE flat_flow CHANGE repository_identifier repository_identifier VARCHAR(255) DEFAULT NULL');

        // Add the existing repositories in the table
        $this->addSql(
            'INSERT INTO abstract_code_repository (identifier, type, name, address, organisation) '.
            'SELECT repository_identifier, \'github\',  repository_name, repository_address, repository_organisation FROM flat_flow GROUP BY repository_identifier'
        );

        $this->addSql('ALTER TABLE flat_flow ADD CONSTRAINT FK_3A810084792D71D5 FOREIGN KEY (repository_identifier) REFERENCES abstract_code_repository (identifier)');
        $this->addSql('CREATE INDEX IDX_3A810084792D71D5 ON flat_flow (repository_identifier)');

        // TODO later
        // $this->addSql('ALTER TABLE flat_flow DROP repository_address, DROP repository_organisation, DROP repository_name');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE flat_flow DROP FOREIGN KEY FK_3A810084792D71D5');
        $this->addSql('DROP TABLE abstract_code_repository');
        $this->addSql('DROP INDEX IDX_3A810084792D71D5 ON flat_flow');
        $this->addSql('ALTER TABLE flat_flow ADD repository_address VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD repository_organisation VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD repository_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        // TODO later
        // $this->addSql('ALTER TABLE flat_flow CHANGE repository_identifier repository_identifier VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
