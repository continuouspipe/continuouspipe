<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Command\BuildImageCommand;
use ContinuousPipe\Builder\Event\BuildFailed;
use ContinuousPipe\Builder\Event\ImageBuilt;
use SimpleBus\Message\Bus\MessageBus;

class BuildImageHandler
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param Builder    $builder
     * @param MessageBus $eventBus
     */
    public function __construct(Builder $builder, MessageBus $eventBus)
    {
        $this->builder = $builder;
        $this->eventBus = $eventBus;
    }

    /**
     * @param BuildImageCommand $command
     */
    public function handle(BuildImageCommand $command)
    {
        try {
            $this->builder->build($command->getBuild(), $command->getLogger());

            $this->eventBus->handle(new ImageBuilt(
                $command->getBuild(),
                $command->getLogger()
            ));
        } catch (BuildException $e) {
            $this->eventBus->handle(new BuildFailed($command->getBuild()));
        }
    }
}
