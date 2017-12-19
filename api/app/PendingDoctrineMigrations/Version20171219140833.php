<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171219140833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flat_flow DROP team_name, DROP team_bucket_uuid, DROP user_email, DROP user_bucket_uuid, DROP user_roles');

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flat_flow ADD team_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD team_bucket_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', ADD user_email VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD user_bucket_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', ADD user_roles LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\', CHANGE repository_identifier repository_identifier VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE user_username user_username VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE team_slug team_slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');

    }
}
