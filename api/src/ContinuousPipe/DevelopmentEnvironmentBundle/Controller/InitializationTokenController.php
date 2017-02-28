<?php

namespace ContinuousPipe\DevelopmentEnvironmentBundle\Controller;

use ContinuousPipe\DevelopmentEnvironment\InitializationToken\InitializationTokenFactory;
use ContinuousPipe\DevelopmentEnvironmentBundle\Request\InitializationTokenCreationRequest;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route(service="development_environment.controller.initialization_token")
 */
class InitializationTokenController
{
    /**
     * @var InitializationTokenFactory
     */
    private $initializationTokenFactory;

    /**
     * @param InitializationTokenFactory $initializationTokenFactory
     */
    public function __construct(InitializationTokenFactory $initializationTokenFactory)
    {
        $this->initializationTokenFactory = $initializationTokenFactory;
    }

    /**
     * @Route("/flows/{uuid}/development-environments/{environmentUuid}/initialization-token", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @ParamConverter("user", converter="user")
     * @Security("is_granted('READ', flow)")
     * @View(statusCode=201)
     */
    public function createAction(User $user, string $environmentUuid, InitializationTokenCreationRequest $creationRequest)
    {
        $token = $this->initializationTokenFactory->create(
            Uuid::fromString($environmentUuid),
            $user,
            $creationRequest
        );

        return [
            'token' => $token->toString(),
        ];
    }
}
