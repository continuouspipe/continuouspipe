<?php

namespace ContinuousPipe\River\Tide\Concurrency;

use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Concurrency\Lock\Locker;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use Ramsey\Uuid\UuidInterface;

class PessimisticTransactionManager implements TransactionManager
{
    /**
     * @var TransactionManager
     */
    private $decoratedManager;

    /**
     * @var Locker
     */
    private $locker;

    /**
     * @param TransactionManager $decoratedManager
     * @param Locker $locker
     */
    public function __construct(TransactionManager $decoratedManager, Locker $locker)
    {
        $this->decoratedManager = $decoratedManager;
        $this->locker = $locker;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(UuidInterface $tideUuid, callable $transaction) : Tide
    {
        $lockReference = 'tide-'.$tideUuid->toString();

        return $this->locker->lock($lockReference, function () use ($tideUuid, $transaction) {
            return $this->decoratedManager->apply($tideUuid, $transaction);
        });
    }
}
