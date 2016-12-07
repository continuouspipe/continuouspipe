<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\Repository\FlowRepository;
use Ramsey\Uuid\UuidInterface;

class FromFlowAggregateFlatFlowRepository implements FlatFlowRepository
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
    public function find(UuidInterface $uuid)
    {
        return FlatFlow::fromFlow(
            $this->flowRepository->find($uuid)
        );
    }
}
