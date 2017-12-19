<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171204142750 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE account_link (username VARCHAR(255) NOT NULL, account_uuid VARCHAR(255) NOT NULL, INDEX IDX_BA3F1E5B5DECD70C (account_uuid), INDEX account_by_username (username), PRIMARY KEY(account_uuid, username)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_invitation (uuid VARCHAR(255) NOT NULL, user_email VARCHAR(255) NOT NULL, team_slug VARCHAR(255) NOT NULL, permissions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account (uuid VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, identifier VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, picture_url VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_api_key (uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id VARCHAR(255) DEFAULT NULL, api_key VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', description LONGTEXT DEFAULT NULL, INDEX IDX_911FF397A76ED395 (user_id), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bucket (uuid VARCHAR(255) NOT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bucket_docker_registries (bucket_uuid VARCHAR(255) NOT NULL, docker_registry_id INT NOT NULL, INDEX IDX_6F8650FD98752A70 (bucket_uuid), INDEX IDX_6F8650FD955355F (docker_registry_id), PRIMARY KEY(bucket_uuid, docker_registry_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bucket_github_tokens (bucket_uuid VARCHAR(255) NOT NULL, github_token_id INT NOT NULL, INDEX IDX_759110C698752A70 (bucket_uuid), INDEX IDX_759110C6AB04A918 (github_token_id), PRIMARY KEY(bucket_uuid, github_token_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bucket_clusters (bucket_uuid VARCHAR(255) NOT NULL, cluster_id INT NOT NULL, INDEX IDX_433F474298752A70 (bucket_uuid), INDEX IDX_433F4742C36A3328 (cluster_id), PRIMARY KEY(bucket_uuid, cluster_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cluster (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(255) NOT NULL, policies LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_policies)\', type VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, version VARCHAR(255) DEFAULT NULL, ca_certificate LONGTEXT DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, client_certificate LONGTEXT DEFAULT NULL, google_cloud_service_account LONGTEXT DEFAULT NULL, management_username VARCHAR(255) DEFAULT NULL, management_password VARCHAR(255) DEFAULT NULL, management_client_certificate LONGTEXT DEFAULT NULL, management_google_cloud_service_account LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE docker_registry (id INT AUTO_INCREMENT NOT NULL, server_address VARCHAR(255) DEFAULT NULL, full_address VARCHAR(255) DEFAULT NULL, username VARCHAR(255) NOT NULL, password LONGTEXT NOT NULL, email VARCHAR(255) NOT NULL, attributes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE git_hub_token (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(255) NOT NULL, access_token VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (slug VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, bucket_uuid VARCHAR(255) NOT NULL, PRIMARY KEY(slug)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_membership (user_id VARCHAR(255) NOT NULL, team_id VARCHAR(255) NOT NULL, permissions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_B826A040A76ED395 (user_id), INDEX IDX_B826A040296CD8AE (team_id), PRIMARY KEY(user_id, team_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE security_user (username VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_52825A88A76ED395 (user_id), PRIMARY KEY(username)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cp_user (username VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, bucket_uuid VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(username)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_billing_profile (uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, creation_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', trial_end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', tides_per_hour INT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, plan_identifier VARCHAR(255) DEFAULT NULL, plan_name VARCHAR(255) DEFAULT NULL, plan_price DOUBLE PRECISION DEFAULT NULL, plan_metrics_tides INT DEFAULT NULL, plan_metrics_memory INT DEFAULT NULL, plan_metrics_docker_image INT DEFAULT NULL, plan_metrics_storage INT DEFAULT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_profile_teams (billing_profile_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', team_slug VARCHAR(255) NOT NULL, INDEX IDX_AC74EC52D2E094C7 (billing_profile_uuid), UNIQUE INDEX UNIQ_AC74EC52497C6F19 (team_slug), PRIMARY KEY(billing_profile_uuid, team_slug)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_billing_profiles (billing_profile_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_username VARCHAR(255) NOT NULL, INDEX IDX_EEF9AE11D2E094C7 (billing_profile_uuid), INDEX IDX_EEF9AE1118D3E277 (user_username), PRIMARY KEY(billing_profile_uuid, user_username)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account_link ADD CONSTRAINT FK_BA3F1E5B5DECD70C FOREIGN KEY (account_uuid) REFERENCES account (uuid)');
        $this->addSql('ALTER TABLE user_api_key ADD CONSTRAINT FK_911FF397A76ED395 FOREIGN KEY (user_id) REFERENCES cp_user (username)');
        $this->addSql('ALTER TABLE bucket_docker_registries ADD CONSTRAINT FK_6F8650FD98752A70 FOREIGN KEY (bucket_uuid) REFERENCES bucket (uuid)');
        $this->addSql('ALTER TABLE bucket_docker_registries ADD CONSTRAINT FK_6F8650FD955355F FOREIGN KEY (docker_registry_id) REFERENCES docker_registry (id)');
        $this->addSql('ALTER TABLE bucket_github_tokens ADD CONSTRAINT FK_759110C698752A70 FOREIGN KEY (bucket_uuid) REFERENCES bucket (uuid)');
        $this->addSql('ALTER TABLE bucket_github_tokens ADD CONSTRAINT FK_759110C6AB04A918 FOREIGN KEY (github_token_id) REFERENCES git_hub_token (id)');
        $this->addSql('ALTER TABLE bucket_clusters ADD CONSTRAINT FK_433F474298752A70 FOREIGN KEY (bucket_uuid) REFERENCES bucket (uuid)');
        $this->addSql('ALTER TABLE bucket_clusters ADD CONSTRAINT FK_433F4742C36A3328 FOREIGN KEY (cluster_id) REFERENCES cluster (id)');
        $this->addSql('ALTER TABLE team_membership ADD CONSTRAINT FK_B826A040A76ED395 FOREIGN KEY (user_id) REFERENCES cp_user (username)');
        $this->addSql('ALTER TABLE team_membership ADD CONSTRAINT FK_B826A040296CD8AE FOREIGN KEY (team_id) REFERENCES team (slug) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE security_user ADD CONSTRAINT FK_52825A88A76ED395 FOREIGN KEY (user_id) REFERENCES cp_user (username)');
        $this->addSql('ALTER TABLE billing_profile_teams ADD CONSTRAINT FK_AC74EC52D2E094C7 FOREIGN KEY (billing_profile_uuid) REFERENCES user_billing_profile (uuid)');
        $this->addSql('ALTER TABLE billing_profile_teams ADD CONSTRAINT FK_AC74EC52497C6F19 FOREIGN KEY (team_slug) REFERENCES team (slug) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_billing_profiles ADD CONSTRAINT FK_EEF9AE11D2E094C7 FOREIGN KEY (billing_profile_uuid) REFERENCES user_billing_profile (uuid)');
        $this->addSql('ALTER TABLE user_billing_profiles ADD CONSTRAINT FK_EEF9AE1118D3E277 FOREIGN KEY (user_username) REFERENCES cp_user (username)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_link DROP FOREIGN KEY FK_BA3F1E5B5DECD70C');
        $this->addSql('ALTER TABLE bucket_docker_registries DROP FOREIGN KEY FK_6F8650FD98752A70');
        $this->addSql('ALTER TABLE bucket_github_tokens DROP FOREIGN KEY FK_759110C698752A70');
        $this->addSql('ALTER TABLE bucket_clusters DROP FOREIGN KEY FK_433F474298752A70');
        $this->addSql('ALTER TABLE bucket_clusters DROP FOREIGN KEY FK_433F4742C36A3328');
        $this->addSql('ALTER TABLE bucket_docker_registries DROP FOREIGN KEY FK_6F8650FD955355F');
        $this->addSql('ALTER TABLE bucket_github_tokens DROP FOREIGN KEY FK_759110C6AB04A918');
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY FK_B826A040296CD8AE');
        $this->addSql('ALTER TABLE billing_profile_teams DROP FOREIGN KEY FK_AC74EC52497C6F19');
        $this->addSql('ALTER TABLE user_api_key DROP FOREIGN KEY FK_911FF397A76ED395');
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY FK_B826A040A76ED395');
        $this->addSql('ALTER TABLE security_user DROP FOREIGN KEY FK_52825A88A76ED395');
        $this->addSql('ALTER TABLE user_billing_profiles DROP FOREIGN KEY FK_EEF9AE1118D3E277');
        $this->addSql('ALTER TABLE billing_profile_teams DROP FOREIGN KEY FK_AC74EC52D2E094C7');
        $this->addSql('ALTER TABLE user_billing_profiles DROP FOREIGN KEY FK_EEF9AE11D2E094C7');
        $this->addSql('DROP TABLE account_link');
        $this->addSql('DROP TABLE user_invitation');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE user_api_key');
        $this->addSql('DROP TABLE bucket');
        $this->addSql('DROP TABLE bucket_docker_registries');
        $this->addSql('DROP TABLE bucket_github_tokens');
        $this->addSql('DROP TABLE bucket_clusters');
        $this->addSql('DROP TABLE cluster');
        $this->addSql('DROP TABLE docker_registry');
        $this->addSql('DROP TABLE git_hub_token');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_membership');
        $this->addSql('DROP TABLE security_user');
        $this->addSql('DROP TABLE cp_user');
        $this->addSql('DROP TABLE user_billing_profile');
        $this->addSql('DROP TABLE billing_profile_teams');
        $this->addSql('DROP TABLE user_billing_profiles');
    }
}
