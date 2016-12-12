<?php

namespace ContinuousPipe\River\Flow\Migrations\ToEventSourced;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Infrastructure\Doctrine\Repository\DoctrineFlowRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;

class DelegateIfNotFoundFlowRepository implements FlowRepository
{
    /**
     * @var Flow\EventBasedFlowRepository
     */
    private $decorated;

    /**
     * @var FlowRepository
     */
    private $delegates;

    /**
     * @param Flow\EventBasedFlowRepository $decorated
     * @param FlowRepository $delegates
     */
    public function __construct(Flow\EventBasedFlowRepository $decorated, FlowRepository $delegates)
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
        return $this->delegates->save($flow);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        return $this->delegates->findByTeam($team);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Flow $flow)
    {
        return $this->delegates->remove($flow);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $codeRepository)
    {
        return $this->delegates->findByCodeRepository($codeRepository);
    }
}
