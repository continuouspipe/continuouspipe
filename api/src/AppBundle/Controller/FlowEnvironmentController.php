<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Flow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function listAction(Flow $flow)
    {
        return $this->environmentClient->findByFlow($flow);
    }

    /**
     * @Route("/flows/{uuid}/environments", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("environment", converter="fos_rest.request_body")
     * @Security("is_granted('DELETE', flow)")
     * @View
     */
    public function deleteAction(Flow $flow, DeployedEnvironment $environment)
    {
        $this->environmentClient->delete($flow, $environment);
    }
}
