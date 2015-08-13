<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\DockerCompose\Loader\YamlLoader;
use ContinuousPipe\Pipe\Command\StartDeploymentCommand;
use ContinuousPipe\Pipe\Deployment;
use ContinuousPipe\Pipe\DeploymentRepository;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Request\EnvironmentRequest;
use ContinuousPipe\User\Security\UserContext;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
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
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @var UserContext
     */
    private $userContext;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param DeploymentRepository $deploymentRepository
     * @param UserContext $userContext
     * @param MessageBus $commandBus
     */
    public function __construct(DeploymentRepository $deploymentRepository, UserContext $userContext, MessageBus $commandBus)
    {
        $this->deploymentRepository = $deploymentRepository;
        $this->userContext = $userContext;
        $this->commandBus = $commandBus;
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
        $deployment = Deployment::fromRequest($deploymentRequest, $this->userContext->getCurrent());
        $this->deploymentRepository->save($deployment);

        $this->commandBus->handle(new StartDeploymentCommand($deployment));

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
