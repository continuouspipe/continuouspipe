<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="admin.controller.flow")
 */
class FlowController
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param TeamRepository $teamRepository
     * @param FlowRepository $flowRepository
     */
    public function __construct(TeamRepository $teamRepository, FlowRepository $flowRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @Route("/teams/{team}/flows", name="admin_flows")
     * @Template
     */
    public function listAction($team)
    {
        $team = $this->teamRepository->find($team);

        return [
            'team' => $team,
            'flows' => $this->flowRepository->findByTeam($team),
        ];
    }
}
