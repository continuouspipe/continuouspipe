<?php

namespace ContinuousPipe\River\Flow\Aggregate;

use ContinuousPipe\Events\Aggregate;
use ContinuousPipe\Events\AggregateNotFound;
use ContinuousPipe\Events\AggregateRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use Ramsey\Uuid\Uuid;

class FlowAggregateRepository implements AggregateRepository
{
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param FlowRepository $flowRepository
     */
    public function __construct(FlowRepository $flowRepository)
    {
        $this->flowRepository = $flowRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $aggregateIdentifier): Aggregate
    {
        try {
            return $this->flowRepository->find(Uuid::fromString($aggregateIdentifier));
        } catch (FlowNotFound $e) {
            throw new AggregateNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
