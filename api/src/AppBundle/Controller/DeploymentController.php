<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\DockerCompose\Loader\YamlLoader;
use ContinuousPipe\Pipe\Deployment;
use ContinuousPipe\Pipe\DeploymentRepository;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Request\EnvironmentRequest;
use Rhumsaa\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.deployment")
 */
class DeploymentController extends Controller
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
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @param ProviderRepository $providerRepository
     * @param YamlLoader $dockerComposeYamlLoader
     * @param EnvironmentClientFactory $environmentClientFactory
     * @param DeploymentRepository $deploymentRepository
     */
    public function __construct(ProviderRepository $providerRepository, YamlLoader $dockerComposeYamlLoader, EnvironmentClientFactory $environmentClientFactory, DeploymentRepository $deploymentRepository)
    {
        $this->providerRepository = $providerRepository;
        $this->dockerComposeYamlLoader = $dockerComposeYamlLoader;
        $this->environmentClientFactory = $environmentClientFactory;
        $this->deploymentRepository = $deploymentRepository;
    }

    /**
     * Creates a new deployment.
     *
     * @Route("/deployments", methods={"POST"})
     * @ParamConverter("deploymentRequest", converter="fos_rest.request_body")
     * @View
     */
    public function createAction(DeploymentRequest $deploymentRequest)
    {
        $deployment = Deployment::fromRequest($deploymentRequest);
        $this->deploymentRepository->save($deployment);

        $environment = $this->dockerComposeYamlLoader->load($deploymentRequest->getEnvironmentName(), $deploymentRequest->getDockerComposeContents());

        $provider = $this->providerRepository->find($deploymentRequest->getProviderName());
        $environmentClient = $this->environmentClientFactory->getByProvider($provider);
        $environmentClient->createOrUpdate($environment);

        return $deployment;
    }

    /**
     * Get a deployment.
     *
     * @Route("/deployments/{uuid}", methods={"GET"})
     * @View
     */
    public function getAction($uuid)
    {
        return $this->deploymentRepository->find(Uuid::fromString($uuid));
    }
}
