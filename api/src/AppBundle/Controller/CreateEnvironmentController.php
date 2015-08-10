<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\DockerCompose\Loader\YamlLoader;
use ContinuousPipe\Pipe\Request\EnvironmentRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * Updates or create an environment with the given name, provider and configuration.
     *
     * @Route("/environments", methods={"PUT"})
     * @ParamConverter("environmentRequest", converter="fos_rest.request_body")
     * @View
     */
    public function createOrUpdateAction(EnvironmentRequest $environmentRequest)
    {
        $environment = $this->dockerComposeYamlLoader->load($environmentRequest->getName(), $environmentRequest->getDockerComposeContents());

        $provider = $this->providerRepository->find($environmentRequest->getProviderName());
        $environmentClient = $this->environmentClientFactory->getByProvider($provider);

        return $environmentClient->createOrUpdate($environment);
    }
}
