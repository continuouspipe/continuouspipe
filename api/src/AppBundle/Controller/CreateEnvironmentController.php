<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\DockerCompose\Loader\YamlLoader;
use ContinuousPipe\Pipe\ProviderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.create_environment")
 */
class CreateEnvironmentController extends Controller
{
    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @var YamlLoader
     */
    private $dockerComposeYamlLoader;

    /**
     * @var EnvironmentClientFactory
     */
    private $environmentClientFactory;

    /**
     * @param ProviderRepository       $providerRepository
     * @param YamlLoader               $dockerComposeYamlLoader
     * @param EnvironmentClientFactory $environmentClientFactory
     */
    public function __construct(ProviderRepository $providerRepository, YamlLoader $dockerComposeYamlLoader, EnvironmentClientFactory $environmentClientFactory)
    {
        $this->providerRepository = $providerRepository;
        $this->dockerComposeYamlLoader = $dockerComposeYamlLoader;
        $this->environmentClientFactory = $environmentClientFactory;
    }

    /**
     * @Route("/environments/from-compose", methods={"POST"})
     * @View
     */
    public function createFromComposeAction(Request $request)
    {
        $provider = $this->providerRepository->findOneByName($request->request->get('provider'));
        $dockerComposeContents = base64_decode($request->request->get('composeContents'));

        $environment = $this->dockerComposeYamlLoader->load($dockerComposeContents);
        $environmentClient = $this->environmentClientFactory->getByProvider($provider);

        return $environmentClient->create($environment);
    }
}
