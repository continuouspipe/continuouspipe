<?php

namespace AppBundle\Controller;

use ContinuousPipe\Pipe\Command\StartDeploymentCommand;
use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Security\Authenticator\UserContext;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\View\View as FOSRestView;

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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param ValidatorInterface   $validator
     * @param DeploymentRepository $deploymentRepository
     * @param UserContext          $userContext
     * @param MessageBus           $commandBus
     */
    public function __construct(ValidatorInterface $validator, DeploymentRepository $deploymentRepository, UserContext $userContext, MessageBus $commandBus)
    {
        $this->deploymentRepository = $deploymentRepository;
        $this->userContext = $userContext;
        $this->commandBus = $commandBus;
        $this->validator = $validator;
    }

    /**
     * Creates a new deployment.
     *
     * @Route("/deployments", methods={"POST"})
     * @ParamConverter("deploymentRequest", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(DeploymentRequest $deploymentRequest)
    {
        $violations = $this->validator->validate($deploymentRequest);
        if (count($violations) > 0) {
            return FOSRestView::create($violations, 400);
        }

        $deployment = Deployment::fromRequest($deploymentRequest, $this->userContext->getCurrent());
        $this->deploymentRepository->save($deployment);

        $this->commandBus->handle(new StartDeploymentCommand($deployment));

        return $this->deploymentRepository->find($deployment->getUuid());
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
