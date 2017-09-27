<?php

namespace ContinuousPipe\AuditLog\EventListener;

use ContinuousPipe\AuditLog\Exception\OperationFailedException;
use ContinuousPipe\AuditLog\RecordFactory;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SaveLogRecord implements EventSubscriberInterface
{

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var RecordFactory
     */
    private $recordFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LogRepository $logRepository, RecordFactory $recordFactory, LoggerInterface $logger)
    {
        $this->logRepository = $logRepository;
        $this->recordFactory = $recordFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserCreated::EVENT_NAME => 'onUserCreated',
        ];
    }

    public function onUserCreated(UserCreated $event)
    {
        try {
            $record = $this->recordFactory->createFromUserCreatedEvent($event);
            $this->logRepository->insert($record);
        } catch (OperationFailedException $e) {
            $this->logger->warning(sprintf(
                'Failed to insert audit log for new user creation (%s).',
                $event->getUser()->getUsername()
            ));
        }
    }
}
