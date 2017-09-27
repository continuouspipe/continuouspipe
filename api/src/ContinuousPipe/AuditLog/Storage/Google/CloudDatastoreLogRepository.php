<?php

namespace ContinuousPipe\AuditLog\Storage\Google;

use ContinuousPipe\AuditLog\Exception\OperationFailedException;
use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use DomainException;
use Google\Cloud\Datastore\DatastoreClient;

/**
 * Repository class for storing audit log records by Google Cloud Datastore service.
 */
class CloudDatastoreLogRepository implements LogRepository
{

    /**
     * @var DatastoreClient
     */
    private $client;

    public function __construct(DatastoreClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Record $record)
    {
        try {
            $key = $this->client->key($record->type());
            $entity = $this->client->entity($key, $record->data());
            $this->client->insert($entity);
        } catch (DomainException $e) {
            throw new OperationFailedException('Failed to insert new record.', 0, $e);
        }
    }
}
