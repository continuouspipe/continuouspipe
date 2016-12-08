<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
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

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team) : array
    {
        return array_map(function (Flow $flow) {
            return FlatFlow::fromFlow($flow);
        }, $this->flowRepository->findByTeam($team));
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $repository) : array
    {
        return array_map(function (Flow $flow) {
            return FlatFlow::fromFlow($flow);
        }, $this->flowRepository->findByCodeRepository($repository));
    }

    /**
     * {@inheritdoc}
     */
    public function remove(UuidInterface $uuid)
    {
        return $this->flowRepository->remove(
            $this->flowRepository->find($uuid)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(FlatFlow $flow)
    {
        throw new \RuntimeException('Do not save!');
    }
}
