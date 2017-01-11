<?php

namespace ContinuousPipe\Events\Transaction;

use ContinuousPipe\Events\Aggregate;
use ContinuousPipe\Events\AggregateRepository;
use SimpleBus\Message\Bus\MessageBus;

class PopAndDispatchEventsTransactionManager implements TransactionManager
{
    /**
     * @var AggregateRepository
     */
    private $aggregateRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param AggregateRepository $aggregateRepository
     * @param MessageBus $eventBus
     */
    public function __construct(AggregateRepository $aggregateRepository, MessageBus $eventBus)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(string $aggregateIdentifier, callable $transaction) : Aggregate
    {
        $aggregate = $this->aggregateRepository->find($aggregateIdentifier);

        if (null !== ($result = $transaction($aggregate))) {
            if (!$aggregate instanceof Aggregate) {
                throw new TransactionException('The transaction have to return `null` or with an `Aggregate` object');
            }

            $aggregate = $result;
        }

        foreach ($aggregate->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $aggregate;
    }
}
