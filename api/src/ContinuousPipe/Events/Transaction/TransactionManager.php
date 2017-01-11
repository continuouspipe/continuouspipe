<?php

namespace ContinuousPipe\Events\Transaction;

use ContinuousPipe\Events\Aggregate;

interface TransactionManager
{
    /**
     * Apply the following transaction on the given tide. This will put a lock on the tide,
     * fetch it, run the transaction, dispatch the events, and release the lock.
     *
     * The `transaction` should be a callable that receives a `Aggregate` object
     * as the first argument and return it.
     *
     * @param string $aggregateIdentifier
     * @param callable $transaction
     *
     * @throws TransactionException
     *
     * @return Aggregate
     */
    public function apply(string $aggregateIdentifier, callable $transaction) : Aggregate;
}
