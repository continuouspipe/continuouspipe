<?php

namespace ContinuousPipe\River\Flow\Migrations\ToEventSourced;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\UuidInterface;

class FromFlowAggregateFlatFlowRepository implements FlatFlowRepository
{
    /**
     * @var FlatFlowRepository
     */
    private $decoratedRepository;

    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param FlatFlowRepository $decoratedRepository
     * @param FlowRepository $flowRepository
     */
    public function __construct(FlatFlowRepository $decoratedRepository, FlowRepository $flowRepository)
    {
        $this->flowRepository = $flowRepository;
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        try {
            return $this->decoratedRepository->find($uuid);
        } catch (FlowNotFound $e) {
            return FlatFlow::fromFlow(
                $this->flowRepository->find($uuid)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team) : array
    {
        $flows = $this->decoratedRepository->findByTeam($team);

        if (count($flows) == 0) {
            $flows = array_map(function (Flow $flow) {
                return FlatFlow::fromFlow($flow);
            }, $this->flowRepository->findByTeam($team));
        }

        return $flows;
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $repository) : array
    {
        $flows = $this->decoratedRepository->findByCodeRepository($repository);

        if (count($flows) == 0) {
            $flows = array_map(function (Flow $flow) {
                return FlatFlow::fromFlow($flow);
            }, $this->flowRepository->findByCodeRepository($repository));
        }

        return $flows;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(UuidInterface $uuid)
    {
        return $this->decoratedRepository->remove($uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function save(FlatFlow $flow)
    {
        return $this->decoratedRepository->save($flow);
    }
}
