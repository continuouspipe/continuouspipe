<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\AdapterRegistry;
use ContinuousPipe\Pipe\ProviderRepository;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
