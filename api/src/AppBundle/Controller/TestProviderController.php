<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.test_provider")
 */
class TestProviderController extends Controller
{
    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @var EnvironmentClientFactory
     */
    private $environmentClientFactory;

    /**
     * @param ProviderRepository       $providerRepository
     * @param EnvironmentClientFactory $environmentClientFactory
     */
    public function __construct(ProviderRepository $providerRepository, EnvironmentClientFactory $environmentClientFactory)
    {
        $this->providerRepository = $providerRepository;
        $this->environmentClientFactory = $environmentClientFactory;
    }

    /**
     * @Route("/providers/{identifier}/test", methods={"POST"}, requirements={"identifier"=".+"})
     * @View
     */
    public function listAction($identifier)
    {
        $provider = $this->providerRepository->find($identifier);
        $environmentClient = $this->environmentClientFactory->getByProvider($provider);
        $environments = $environmentClient->findAll();

        return [
            'message' => 'Successfully got environements',
            'environments' => $environments,
        ];
    }
}
