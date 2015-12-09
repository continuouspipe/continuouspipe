<?php

namespace ContinuousPipe\Builder\Logging\LogStream;

use FaultTolerance\OperationRunner\RetryOperationRunner;
use FaultTolerance\OperationRunner\SimpleOperationRunner;
use FaultTolerance\Waiter\SleepWaiter;
use FaultTolerance\WaitStrategy\Exponential;
use FaultTolerance\WaitStrategy\Max;

class OperationRunnerFactory
{
    /**
     * @return RetryOperationRunner
     */
    public static function create()
    {
        return new RetryOperationRunner(
            new SimpleOperationRunner(),
            new Max(
                new Exponential(
                    new SleepWaiter(),
                    0.1
                ),
                10
            )
        );
    }
}
