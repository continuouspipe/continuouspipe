<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Event\FlowCreated;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.create_flow")
 */
class CreateFlowController
{
    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param FlowRepository $flowRepository
     * @param MessageBus     $eventBus
     */
    public function __construct(FlowRepository $flowRepository, MessageBus $eventBus)
    {
        $this->flowRepository = $flowRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * Create a new flow from a repository.
     *
     * @Route("/flow/from-repository/{identifier}", methods={"POST"})
     * @ParamConverter("user", converter="user")
     * @ParamConverter("repository", converter="code-repository", options={"identifier"="identifier"})
     * @View
     */
    public function fromRepositoryAction(User $user, CodeRepository $repository)
    {
        $flow = $this->flowRepository->save(
            Flow::fromUserAndCodeRepository($user, $repository)
        );

        $this->eventBus->handle(new FlowCreated($flow));

        return $flow;
    }
}
