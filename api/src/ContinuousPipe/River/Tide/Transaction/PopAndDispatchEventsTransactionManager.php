<?php

namespace ContinuousPipe\River\Tide\Transaction;

use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\MessageBus;

class PopAndDispatchEventsTransactionManager implements TransactionManager
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;

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
        $tide = $this->tideRepository->find($tideUuid);

        if (null !== ($result = $transaction($tide))) {
            if (!$tide instanceof Tide) {
                throw new TransactionException('The transaction have to return `null` or with a `Tide` object');
            }

            $tide = $result;
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $tide;
    }
}
