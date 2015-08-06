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
 * @Route(service="app.controller.flow")
 */
class FlowController
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

    /**
     * Create a new flow from a repository.
     *
     * @Route("/flows", methods={"GET"})
     * @ParamConverter("user", converter="user")
     * @View
     */
    public function listAction(User $user)
    {
        return $this->flowRepository->findByUser($user);
    }

    /**
     * Delete a flow.
     *
     * @Route("/flows/{uuid}", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @View
     */
    public function deleteAction(Flow $flow)
    {
        return $this->flowRepository->remove($flow);
    }
}
