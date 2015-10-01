<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Command\BuildImageCommand;
use ContinuousPipe\Builder\Event\BuildFailed;
use ContinuousPipe\Builder\Event\ImageBuilt;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Builder         $builder
     * @param MessageBus      $eventBus
     * @param LoggerInterface $logger
     */
    public function __construct(Builder $builder, MessageBus $eventBus, LoggerInterface $logger)
    {
        $this->builder = $builder;
        $this->eventBus = $eventBus;
        $this->logger = $logger;
    }

    /**
     * @param BuildImageCommand $command
     */
    public function handle(BuildImageCommand $command)
    {
        $this->logger->info('Building an image', [
            'build' => $command->getBuild(),
        ]);

        try {
            $this->builder->build($command->getBuild(), $command->getLogger());

            $this->eventBus->handle(new ImageBuilt(
                $command->getBuild(),
                $command->getLogger()
            ));
        } catch (BuildException $e) {
            $this->logger->notice('An error appeared while building an image', [
                'build' => $command->getBuild(),
                'exception' => $e,
            ]);

            $this->eventBus->handle(new BuildFailed($command->getBuild()));
        }
    }
}
