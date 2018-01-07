<?php

namespace Application\Migrations;

use ContinuousPipe\River\Infrastructure\Doctrine\UuidUpgrade\UuidReplacer;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160529132322 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $results = $this->connection->fetchAll('SELECT uuid, context FROM flow_dto');
        foreach ($results as $row) {
            $context = unserialize(base64_decode($row['context']));
            $context = UuidReplacer::replace($context);

            $row['context'] = base64_encode(serialize($context));
            $this->connection->update('flow_dto', ['context' => $row['context']], ['uuid' => $row['uuid']]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
