<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\Notifier;
use LogStream\EmptyLogger;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Container;
use LogStream\Node\Text;
use LogStream\WrappedLog;

class BuildHandler
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Builder         $builder
     * @param Notifier        $notifier
     * @param BuildRepository $buildRepository
     * @param LoggerFactory   $loggerFactory
     */
    public function __construct(Builder $builder, Notifier $notifier, BuildRepository $buildRepository, LoggerFactory $loggerFactory)
    {
        $this->builder = $builder;
        $this->notifier = $notifier;
        $this->buildRepository = $buildRepository;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param BuildCommand $command
     */
    public function handle(BuildCommand $command)
    {
        $build = $command->getBuild();

        $logger = $this->getLogger($build);
        $logger->start();

        $build->updateStatus(Build::STATUS_RUNNING);
        $build = $this->buildRepository->save($build);

        try {
            $this->builder->build($build, $logger);

            $build->updateStatus(Build::STATUS_SUCCESS);
        } catch (\Exception $e) {
            $logger->append(new Text($e->getMessage()));

            $build->updateStatus(Build::STATUS_ERROR);
        } finally {
            $build = $this->buildRepository->save($build);
        }

        $notification = $build->getRequest()->getNotification();
        if (null !== $notification) {
            $this->notifier->notify($notification, $build);
        }
    }

    /**
     * Get logger for that given build.
     *
     * @param Build $build
     *
     * @return Logger
     */
    private function getLogger(Build $build)
    {
        if ($logging = $build->getRequest()->getLogging()) {
            if ($logStream = $logging->getLogstream()) {
                return $this->loggerFactory->from(
                    new WrappedLog($logStream->getParentLogIdentifier(), new Container())
                );
            }
        }

        return new EmptyLogger();
    }
}
