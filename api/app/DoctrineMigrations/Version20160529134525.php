<?php

namespace Application\Migrations;

use ContinuousPipe\River\Infrastructure\Doctrine\UuidUpgrade\UuidReplacer;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160529134525 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $results = $this->connection->fetchAll('SELECT uuid, serialized_user, serialized_team, code_reference_code_repository FROM tide_dto');
        foreach ($results as $row) {
            $this->connection->update('tide_dto', [
                'serialized_user' => $this->replace($row['serialized_user']),
                'serialized_team' => $this->replace($row['serialized_team']),
                'code_reference_code_repository' => $this->replace($row['code_reference_code_repository']),
            ], ['uuid' => $row['uuid']]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }

    /**
     * @param string $serializedObject
     *
     * @return string
     */
    private function replace($serializedObject)
    {
        return base64_encode(serialize(UuidReplacer::replace(
            unserialize(base64_decode($serializedObject))
        )));
    }
}
