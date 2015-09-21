<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Command\BuildImageCommand;
use ContinuousPipe\Builder\Event\BuildFailed;
use ContinuousPipe\Builder\Event\ImageBuilt;
use LogStream\Node\Text;
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
        $logger = $command->getLogger();

        try {
            $this->builder->build($command->getBuild(), $logger);

            $this->eventBus->handle(new ImageBuilt(
                $command->getBuild(),
                $command->getLogger()
            ));
        } catch (BuildException $e) {
            $logger->append(new Text($e->getMessage()));

            $this->eventBus->handle(new BuildFailed($command->getBuild()));
        }
    }
}
