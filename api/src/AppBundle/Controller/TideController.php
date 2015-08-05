<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Repository\TideRepository;
use Rhumsaa\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     * Get a tide by its UUID.
     *
     * @Route("/tide/{uuid}", methods={"GET"})
     * @View
     */
    public function fromRepositoryAction($uuid)
    {
        return $this->tideRepository->find(Uuid::fromString($uuid));
    }
}
