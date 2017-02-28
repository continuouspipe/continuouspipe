<?php

namespace ContinuousPipe\DevelopmentEnvironment\ReadModel;

use ContinuousPipe\DevelopmentEnvironment\Aggregate\DevelopmentEnvironmentRepository as AggregateDevelopmentEnvironmentRepository;
use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironmentRepository as ReadModelDevelopmentEnvironmentRepository;
use ContinuousPipe\DevelopmentEnvironment\Aggregate\Events\DevelopmentEnvironmentEvent;

class CreateProjectionWhenAnEventIsDispatched
{
    /**
     * @var AggregateDevelopmentEnvironmentRepository
     */
    private $aggregateRepository;

    /**
     * @var DevelopmentEnvironmentRepository
     */
    private $readModelRepository;

    public function __construct(AggregateDevelopmentEnvironmentRepository $aggregateRepository, ReadModelDevelopmentEnvironmentRepository $readModelRepository)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->readModelRepository = $readModelRepository;
    }

    public function notify(DevelopmentEnvironmentEvent $event)
    {
        $aggregate = $this->aggregateRepository->find($event->getDevelopmentEnvironmentUuid());

        $this->readModelRepository->save($aggregate->createView());
    }
}
