<?php

namespace ContinuousPipe\River\Team\Request;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\Security\Team\Team;
use Symfony\Component\Validator\Constraints as Assert;

class TeamDeletionRequest
{
    /**
     * @var Team
     */
    private $team;

    /**
     * @var FlatFlowRepository
     */
    private $flowRepository;

    public function __construct(Team $team, FlatFlowRepository $flowRepository)
    {
        $this->team = $team;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @Assert\Count(
     *     min=0,
     *     max=0,
     *     exactMessage="The team cannot be deleted, because it has flows associated with it. Delete these flows first."
     * )
     *
     * @return \ContinuousPipe\River\Flow\Projections\FlatFlow[]
     */
    public function getFlows()
    {
        return $this->flowRepository->findByTeam($this->team);
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }
}
