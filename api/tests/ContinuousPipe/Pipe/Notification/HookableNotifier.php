<?php

namespace ContinuousPipe\Pipe\Notification;

use ContinuousPipe\Pipe\View\Deployment;

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
    public function notify($address, Deployment $deployment)
    {
        foreach ($this->hooks as $hook) {
            $hook();
        }

        return $this->decoratedNotifier->notify($address, $deployment);
    }

    /**
     * @param callable $hook
     */
    public function addHook(callable $hook)
    {
        $this->hooks[] = $hook;
    }
}
