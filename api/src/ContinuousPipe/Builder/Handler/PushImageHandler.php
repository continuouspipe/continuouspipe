<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Command\PushImageCommand;
use ContinuousPipe\Builder\Event\BuildFailed;
use ContinuousPipe\Builder\Event\ImagePushed;
use SimpleBus\Message\Bus\MessageBus;

class PushImageHandler
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
     * @param PushImageCommand $command
     */
    public function handle(PushImageCommand $command)
    {
        try {
            $this->builder->push($command->getBuild(), $command->getLogger());

            $this->eventBus->handle(new ImagePushed(
                $command->getBuild()
            ));
        } catch (BuildException $e) {
            $this->eventBus->handle(new BuildFailed($command->getBuild()));
        }
    }
}
