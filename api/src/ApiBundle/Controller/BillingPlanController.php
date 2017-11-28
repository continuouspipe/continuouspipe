<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Billing\Plan\Repository\PlanRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.billing_plan")
 */
class BillingPlanController
{
    /**
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * @param PlanRepository $planRepository
     */
    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * @Route("/billing/plans", methods={"GET"})
     * @View
     */
    public function getPlansAction()
    {
        return $this->planRepository->findPlans();
    }

    /**
     * @Route("/billing/add-ons", methods={"GET"})
     * @View
     */
    public function getAddOnsAction()
    {
        return $this->planRepository->findAddOns();
    }
}
