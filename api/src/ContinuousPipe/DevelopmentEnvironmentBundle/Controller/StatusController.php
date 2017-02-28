<?php

namespace ContinuousPipe\DevelopmentEnvironmentBundle\Controller;

use ContinuousPipe\DevelopmentEnvironment\InitializationToken\InitializationTokenFactory;
use ContinuousPipe\DevelopmentEnvironment\Status\StatusFetcher;
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
 * @Route(service="development_environment.controller.status")
 */
class StatusController
{
    /**
     * @var StatusFetcher
     */
    private $statusFetcher;

    /**
     * @param StatusFetcher $statusFetcher
     */
    public function __construct(StatusFetcher $statusFetcher)
    {
        $this->statusFetcher = $statusFetcher;
    }

    /**
     * @Route("/flows/{uuid}/development-environments/{environmentUuid}/status", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function createAction(string $environmentUuid)
    {
        return $this->statusFetcher->fetch(
            Uuid::fromString($environmentUuid)
        );
    }
}
