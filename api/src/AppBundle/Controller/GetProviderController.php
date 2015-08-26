<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\Pipe\AdapterProviderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.get_provider")
 */
class GetProviderController extends Controller
{
    /**
     * @var AdapterProviderRepository
     */
    private $providerRepository;

    /**
     * @param AdapterProviderRepository $providerRepository
     */
    public function __construct(AdapterProviderRepository $providerRepository)
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
