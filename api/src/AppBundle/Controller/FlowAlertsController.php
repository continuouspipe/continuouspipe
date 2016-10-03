<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Alerts\AlertsRepository;
use ContinuousPipe\River\Flow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="app.controller.flow.alerts")
 */
class FlowAlertsController
{
    /**
     * @var AlertsRepository
     */
    private $alertsRepository;

    /**
     * @param AlertsRepository $alertsRepository
     */
    public function __construct(AlertsRepository $alertsRepository)
    {
        $this->alertsRepository = $alertsRepository;
    }

    /**
     * Get alerts from a flow.
     *
     * @Route("/flows/{uuid}/alerts", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function getAction(Flow $flow)
    {
        return $this->alertsRepository->findByFlow($flow);
    }
}
