<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
use Rhumsaa\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.tide")
 */
class TideController
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param TideRepository $tideRepository
     */
    public function __construct(TideRepository $tideRepository)
    {
        $this->tideRepository = $tideRepository;
    }

    /**
     * Get tide by flow.
     *
     * @Route("/flows/{uuid}/tides", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @View
     */
    public function findByFlowAction(Flow $flow)
    {
        return $this->tideRepository->findByFlow($flow);
    }

    /**
     * Get a tide by its UUID.
     *
     * @Route("/tides/{uuid}", methods={"GET"})
     * @View
     */
    public function getAction($uuid)
    {
        return $this->tideRepository->find(Uuid::fromString($uuid));
    }
}
