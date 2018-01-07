<?php

namespace ContinuousPipe\River\Tide\Transaction;

use ContinuousPipe\Message\Bridge\Symfony\Events;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PopAndDispatchEventsTransactionManager implements TransactionManager, EventSubscriberInterface
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;

    private $cache = [];

    public function __construct(TideRepository $tideRepository, MessageBus $eventBus)
    {
        $this->tideRepository = $tideRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(UuidInterface $tideUuid, callable $transaction) : Tide
    {
        if (isset($this->cache[(string) $tideUuid])) {
            $tide = $this->cache[(string) $tideUuid];
        } else {
            $tide = $this->tideRepository->find($tideUuid);
        }

        if (null !== ($result = $transaction($tide))) {
            if (!$tide instanceof Tide) {
                throw new TransactionException('The transaction have to return `null` or with a `Tide` object');
            }

            $tide = $result;
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }

        $this->cache[(string) $tideUuid] = $tide;

        return $tide;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_PROCESSED => 'messageProcessed',
        ];
    }

    public function messageProcessed()
    {
        $this->cache = [];
    }
}
