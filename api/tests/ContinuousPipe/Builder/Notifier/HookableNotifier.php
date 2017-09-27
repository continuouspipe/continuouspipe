<?php

namespace ContinuousPipe\Builder\Notifier;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Notifier;

class HookableNotifier implements Notifier
{
    /**
     * @var Notifier
     */
    private $decoratedNotifier;

    /**
     * @var callable[]
     */
    private $hooks = [];

    /**
     * @param Notifier $decoratedNotifier
     */
    public function __construct(Notifier $decoratedNotifier)
    {
        $this->decoratedNotifier = $decoratedNotifier;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Notification $notification, Build $build)
    {
        foreach ($this->hooks as $hook) {
            list($notification, $build) = $hook($notification, $build);
        }

        return $this->decoratedNotifier->notify($notification, $build);
    }

    /**
     * @param callable $hook
     */
    public function addHook(callable $hook)
    {
        $this->hooks[] = $hook;
    }
}
