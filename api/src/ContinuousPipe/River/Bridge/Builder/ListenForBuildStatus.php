<?php

namespace ContinuousPipe\River\Bridge\Builder;

use ContinuousPipe\River\Bridge\Builder\Command\ReportImageBuildCompletion;
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use Ramsey\Uuid\Uuid;
use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\View\BuildViewRepository;
use SimpleBus\Message\Bus\MessageBus;

class ListenForBuildStatus
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var BuildViewRepository
     */
    private $buildRepository;

    public function __construct(MessageBus $commandBus, BuildViewRepository $buildRepository)
    {
        $this->commandBus = $commandBus;
        $this->buildRepository = $buildRepository;
    }

    public function handle(BuildEvent $event)
    {
        $build = $this->buildRepository->find($event->getBuildIdentifier());
        $attributes = $build->getRequest()->getAttributes();

        if (empty($attributes['tide_uuid'])) {
            throw new \RuntimeException('Build was created without a `tide_uuid` attribute');
        }

        $this->commandBus->handle(new ReportImageBuildCompletion(
            Uuid::fromString($attributes['tide_uuid']),
            $event->getBuildIdentifier()
        ));
    }
}
