<?php

namespace Application\Migrations;

use ContinuousPipe\River\Infrastructure\Doctrine\UuidUpgrade\UuidReplacer;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160529133433 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $results = $this->connection->fetchAll('SELECT id, serialized_event, event_datetime FROM event_dto');
        foreach ($results as $row) {
            $event = unserialize(base64_decode($row['serialized_event']));
            $event = UuidReplacer::replace($event);

            $row['serialized_event'] = base64_encode(serialize($event));
            $this->connection->update('event_dto', [
                'serialized_event' => $row['serialized_event'],
                'event_datetime' => $row['event_datetime'],
            ], ['id' => $row['id']]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
