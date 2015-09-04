<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\Flow as FlowView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.flow_environment")
 */
class FlowEnvironmentController
{
    /**
     * @var Flow\EnvironmentClient
     */
    private $environmentClient;

    /**
     * @param Flow\EnvironmentClient $environmentClient
     */
    public function __construct(Flow\EnvironmentClient $environmentClient)
    {
        $this->environmentClient = $environmentClient;
    }

    /**
     * @Route("/flows/{uuid}/environments", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @View
     */
    public function listAction(Flow $flow)
    {
        return $this->environmentClient->findByFlow(FlowView::fromFlow($flow));
    }
}
