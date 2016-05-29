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
        $numberOfEvents = $this->connection->fetchAssoc('SELECT COUNT(*) as number FROM event_dto')['number'];
        $numberOfEventsPerLoop = 100;
        $numberOfLoops = ceil($numberOfEvents / $numberOfEvents);

        for ($i = 0; $i < $numberOfLoops; $i++) {
            $offset = ($i * $numberOfEventsPerLoop);

            $results = $this->connection->fetchAll('SELECT id, serialized_event, event_datetime FROM event_dto LIMIT '.$numberOfEventsPerLoop.' OFFSET '.$offset);
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
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
