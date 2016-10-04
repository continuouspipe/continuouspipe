<?php

namespace ContinuousPipe\Builder\Notifier;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Notifier;
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
    private $decoratedNotifier;

    /**
     * @var Waiter
     */
    private $waiter;

    /**
     * @param Notifier $decoratedNotifier
     * @param Waiter   $waiter
     */
    public function __construct(Notifier $decoratedNotifier, Waiter $waiter)
    {
        $this->decoratedNotifier = $decoratedNotifier;
        $this->waiter = $waiter;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Notification $notification, Build $build)
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

        try {
            $runner->run(new Callback(function () use ($notification, $build) {
                return $this->decoratedNotifier->notify($notification, $build);
            }));
        } catch (NotificationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new NotificationException('Unable to send notification even after retries', $e->getCode(), $e);
        }
    }
}
