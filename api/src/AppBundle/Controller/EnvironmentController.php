<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\Pipe\AdapterProviderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.environment")
 */
class EnvironmentController extends Controller
{
    /**
     * @var AdapterProviderRepository
     */
    private $providerRepository;

    /**
     * @var EnvironmentClientFactory
     */
    private $environmentClientFactory;

    /**
     * @param AdapterProviderRepository $providerRepository
     * @param EnvironmentClientFactory  $environmentClientFactory
     */
    public function __construct(AdapterProviderRepository $providerRepository, EnvironmentClientFactory $environmentClientFactory)
    {
        $this->providerRepository = $providerRepository;
        $this->environmentClientFactory = $environmentClientFactory;
    }

    /**
     * @Route("/providers/{type}/{identifier}/environments", methods={"GET"})
     * @View
     */
    public function listAction($type, $identifier)
    {
        $provider = $this->providerRepository->findByTypeAndIdentifier($type, $identifier);
        $environmentClient = $this->environmentClientFactory->getByProvider($provider);

        return $environmentClient->findAll();
    }
}
