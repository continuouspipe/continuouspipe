<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171219140000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flat_flow CHANGE team_name team_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE team_bucket_uuid team_bucket_uuid CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE user_email user_email VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE user_bucket_uuid user_bucket_uuid CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE user_roles user_roles LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\'');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
