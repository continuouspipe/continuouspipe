<?php

namespace ContinuousPipe\Pipe\Notification;

use ContinuousPipe\Pipe\View\Deployment;
use Tolerance\Operation\Callback;
use Tolerance\Operation\Runner\CallbackOperationRunner;
use Tolerance\Operation\Runner\RetryOperationRunner;
use Tolerance\Waiter\CountLimited;
use Tolerance\Waiter\ExponentialBackOff;
use Tolerance\Waiter\Waiter;

class RetryNotifier implements Notifier
{
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var Waiter
     */
    private $waiter;

    /**
     * @param Notifier $notifier
     * @param Waiter   $waiter
     */
    public function __construct(Notifier $notifier, Waiter $waiter)
    {
        $this->notifier = $notifier;
        $this->waiter = $waiter;
    }

    /**
     * {@inheritdoc}
     */
    public function notify($address, Deployment $deployment)
    {
        $waitStrategy = new CountLimited(
            new ExponentialBackOff(
                $this->waiter,
                1
            ),
            10
        );

        $runner = new RetryOperationRunner(
            new CallbackOperationRunner(),
            $waitStrategy
        );

        return $runner->run(new Callback(function () use ($address, $deployment) {
            $this->notifier->notify($address, $deployment);
        }));
    }
}
