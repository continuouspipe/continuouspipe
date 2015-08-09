<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\ProviderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.get_provider")
 */
class GetProviderController extends Controller
{
    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @param ProviderRepository $providerRepository
     */
    public function __construct(ProviderRepository $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    /**
     * @Route("/providers", methods={"GET"})
     * @View
     */
    public function listAction()
    {
        return $this->providerRepository->findAll();
    }
}
