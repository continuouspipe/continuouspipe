<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171219132816 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE flat_flow ADD CONSTRAINT FK_3A81008418D3E277 FOREIGN KEY (user_username) REFERENCES cp_user (username)');
        $this->addSql('ALTER TABLE flat_flow ADD CONSTRAINT FK_3A810084497C6F19 FOREIGN KEY (team_slug) REFERENCES team (slug)');
        $this->addSql('CREATE INDEX IDX_3A81008418D3E277 ON flat_flow (user_username)');
        $this->addSql('CREATE INDEX IDX_3A810084497C6F19 ON flat_flow (team_slug)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE flat_flow DROP FOREIGN KEY FK_3A81008418D3E277');
        $this->addSql('ALTER TABLE flat_flow DROP FOREIGN KEY FK_3A810084497C6F19');
        $this->addSql('DROP INDEX IDX_3A81008418D3E277 ON flat_flow');
        $this->addSql('DROP INDEX IDX_3A810084497C6F19 ON flat_flow');
    }
}
