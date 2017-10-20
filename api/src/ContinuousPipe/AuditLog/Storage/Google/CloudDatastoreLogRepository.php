<?php

namespace ContinuousPipe\AuditLog\Storage\Google;

use ContinuousPipe\AuditLog\Exception\OperationFailedException;
use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use ContinuousPipe\AuditLog\Storage\PaginatedResult;
use DateTime;
use DateTimeImmutable;
use DomainException;
use Google\Cloud\Core\Exception\BadRequestException;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Core\ServiceBuilder;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;
use Google\Cloud\Datastore\Query\Query;
use function PasswordCompat\binary\check;

/**
 * Repository class for storing audit log records by Google Cloud Datastore service.
 */
class CloudDatastoreLogRepository implements LogRepository
{
    /**
     * @var DatastoreClient|null
     */
    private $client;

    /**
     * @var array
     */
    private $clientOptions;

    public function __construct(array $clientOptions = [])
    {
        $this->clientOptions = $clientOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Record $record)
    {
        try {
            $key = $this->getClient()->key($record->type());
            $entity = $this->getClient()->entity($key, $this->createEntityDataFromRecord($record));
            $this->getClient()->insert($entity);
        } catch (DomainException $e) {
            throw new OperationFailedException('Failed to insert new record.', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $eventType, string $pageCursor, int $pageSize): PaginatedResult
    {
        try {
            $query = $this->getClient()->query()
                ->kind($eventType)
                ->limit($pageSize)
                ->start($pageCursor)
                ->order('event_date', Query::ORDER_DESCENDING);

            $records = [];
            $nextPageCursor = '';
            /** @var Entity $entity */
            foreach ($this->getClient()->runQuery($query) as $entity) {
                if ($entity instanceof Entity) {
                    $records[] = $this->createRecordFromEntity($entity);
                    $nextPageCursor = $entity->cursor();
                }
            }

            return new PaginatedResult($records, $nextPageCursor);
        } catch (BadRequestException $e) {
            throw new OperationFailedException('Failed to run query.', 0, $e);
        }
    }

    protected function createEntityDataFromRecord(Record $record): array
    {
        $data = $record->data();
        $data['event_name'] = $record->name();
        $data['event_date'] = $record->date()->format(DateTime::W3C);
        return $data;
    }

    protected function createRecordFromEntity(Entity $entity): Record
    {
        $eventType = $entity->key()->pathEnd()['kind'];
        $data = $entity->get();
        $eventDate = DateTimeImmutable::createFromFormat(DateTime::W3C, $data['event_date']);
        $eventName = $data['event_name'];
        unset($data['event_date'], $data['event_name']);
        ksort($data);
        return new Record($eventName, $eventType, $data, $eventDate);
    }

    /**
     * {@inheritdoc}
     */
    public function listEventTypes(): array
    {
        return [
            'ContinuousPipe\Authenticator\Event\TeamCreationEvent',
            'ContinuousPipe\Authenticator\Security\Event\UserCreated',
        ];
    }

    private function getClient()
    {
        if (null === $this->client) {
            try {
                $this->client = (new ServiceBuilder())->datastore($this->clientOptions);
            } catch (GoogleException $e) {
                throw new OperationFailedException('Failed to create the Datastore client.', 0, $e);
            }
        }

        return $this->client;
    }
}
