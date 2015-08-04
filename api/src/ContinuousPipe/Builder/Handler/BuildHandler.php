<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\Logging\BuildLoggerFactory;
use ContinuousPipe\Builder\Notifier;
use LogStream\Node\Text;

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
     * @var BuildLoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Builder         $builder
     * @param Notifier        $notifier
     * @param BuildRepository $buildRepository
     * @param BuildLoggerFactory   $loggerFactory
     */
    public function __construct(Builder $builder, Notifier $notifier, BuildRepository $buildRepository, BuildLoggerFactory $loggerFactory)
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

        $logger = $this->loggerFactory->forBuild($build);
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
}
