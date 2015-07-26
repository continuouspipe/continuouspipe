<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\DockerBuilder;
use ContinuousPipe\Builder\Notifier;

class BuildHandler
{
    /**
     * @var DockerBuilder
     */
    private $builder;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @param DockerBuilder $builder
     * @param Notifier $notifier
     */
    public function __construct(DockerBuilder $builder, Notifier $notifier)
    {
        $this->builder = $builder;
        $this->notifier = $notifier;
    }

    /**
     * @param BuildCommand $command
     */
    public function handle(BuildCommand $command)
    {
        $build = $this->builder->build($command->getBuild());

        $notification = $build->getRequest()->getNotification();
        if (null !== $notification) {
            $this->notifier->notify($notification, $build);
        }
    }
}
