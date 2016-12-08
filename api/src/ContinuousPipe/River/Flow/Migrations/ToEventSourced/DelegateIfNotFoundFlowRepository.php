<?php

namespace ContinuousPipe\River\Flow\Migrations\ToEventSourced;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;

class DelegateIfNotFoundFlowRepository implements FlowRepository
{
    /**
     * @var FlowRepository
     */
    private $decorated;

    /**
     * @var FlowRepository
     */
    private $delegates;

    /**
     * @param FlowRepository $decorated
     * @param FlowRepository $delegates
     */
    public function __construct(FlowRepository $decorated, FlowRepository $delegates)
    {
        $this->decorated = $decorated;
        $this->delegates = $delegates;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        try {
            return $this->decorated->find($uuid);
        } catch (FlowNotFound $e) {
            return $this->delegates->find($uuid);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(Flow $flow)
    {
        return $this->decorated->save($flow);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        return $this->decorated->findByTeam($team);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Flow $flow)
    {
        return $this->decorated->remove($flow);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $codeRepository)
    {
        return $this->decorated->findByCodeRepository($codeRepository);
    }
}
