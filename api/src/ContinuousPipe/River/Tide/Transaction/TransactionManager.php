<?php

namespace ContinuousPipe\River\Tide\Transaction;

use ContinuousPipe\River\Tide;
use Ramsey\Uuid\UuidInterface;

interface TransactionManager
{
    /**
     * Apply the following transaction on the given tide. This will put a lock on the tide,
     * fetch it, run the transaction, dispatch the events, and release the lock.
     *
     * The `transaction` should be a callable that receives a `ContinuousPipe\River\Tide` object
     * as the first argument and return it.
     *
     * @param UuidInterface $tideUuid
     * @param callable $transaction
     *
     * @throws TransactionException
     *
     * @return Tide
     */
    public function apply(UuidInterface $tideUuid, callable $transaction) : Tide;
}
