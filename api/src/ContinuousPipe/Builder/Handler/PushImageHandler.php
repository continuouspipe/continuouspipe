<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Command\PushImageCommand;
use ContinuousPipe\Builder\Event\BuildFailed;
use ContinuousPipe\Builder\Event\ImagePushed;
use Psr\Log\LoggerInterface;
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
     * @param PushImageCommand $command
     */
    public function handle(PushImageCommand $command)
    {
        $this->logger->info('Push an image', [
            'build' => $command->getBuild(),
        ]);

        try {
            $this->builder->push($command->getBuild(), $command->getLogger());

            $this->eventBus->handle(new ImagePushed(
                $command->getBuild()
            ));
        } catch (BuildException $e) {
            $this->logger->notice('An error appeared while pushing an image', [
                'build' => $command->getBuild(),
                'exception' => $e,
            ]);

            $this->eventBus->handle(new BuildFailed($command->getBuild()));
        }
    }
}
