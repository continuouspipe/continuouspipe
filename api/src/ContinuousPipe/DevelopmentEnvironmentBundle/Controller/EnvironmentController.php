<?php

namespace ContinuousPipe\DevelopmentEnvironmentBundle\Controller;

use ContinuousPipe\DevelopmentEnvironment\Aggregate\DevelopmentEnvironment;
use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironmentRepository;
use ContinuousPipe\DevelopmentEnvironmentBundle\Request\EnvironmentCreationRequest;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\User\User;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="development_environment.controller.environment")
 */
class EnvironmentController
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var DevelopmentEnvironmentRepository
     */
    private $developmentEnvironmentRepository;

    public function __construct(MessageBus $eventBus, DevelopmentEnvironmentRepository $developmentEnvironmentRepository)
    {
        $this->eventBus = $eventBus;
        $this->developmentEnvironmentRepository = $developmentEnvironmentRepository;
    }

    /**
     * @Route("/flows/{uuid}/development-environments", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @ParamConverter("user", converter="user")
     * @Security("is_granted('READ', flow)")
     * @View(statusCode=201)
     */
    public function createAction(FlatFlow $flow, User $user, EnvironmentCreationRequest $creationRequest)
    {
        $developmentEnvironment = DevelopmentEnvironment::create($flow->getUuid(), $user, $creationRequest->getName());

        foreach ($developmentEnvironment->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $this->developmentEnvironmentRepository->find($developmentEnvironment->getUuid());
    }

    /**
     * @Route("/flows/{uuid}/development-environments", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function listAction(FlatFlow $flow)
    {
        return $this->developmentEnvironmentRepository->findByFlow($flow->getUuid());
    }
}
