<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
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
     * @Route("/teams/{team}/flows", name="admin_flows")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @Template
     */
    public function listAction(Team $team)
    {
        return [
            'team' => $team,
            'flows' => $this->flowRepository->findByTeam($team),
        ];
    }
}
