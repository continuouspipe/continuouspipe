<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Event\FlowCreated;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\Flow as FlowView;
use ContinuousPipe\River\FlowFactory;
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
     * @var FlowFactory
     */
    private $flowFactory;

    /**
     * @param FlowRepository $flowRepository
     * @param FlowFactory    $flowFactory
     * @param MessageBus     $eventBus
     */
    public function __construct(FlowRepository $flowRepository, FlowFactory $flowFactory, MessageBus $eventBus)
    {
        $this->flowRepository = $flowRepository;
        $this->eventBus = $eventBus;
        $this->flowFactory = $flowFactory;
    }

    /**
     * Create a new flow from a repository.
     *
     * @Route("/flows", methods={"POST"})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @View
     */
    public function fromRepositoryAction(Flow\Request\FlowCreationRequest $creationRequest)
    {
        $flow = $this->flowFactory->fromCreationRequest($creationRequest);
        $flow = $this->flowRepository->save($flow);

        $this->eventBus->handle(new FlowCreated($flow));

        return FlowView::fromFlow($flow);
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
        return array_map(function (Flow $flow) {
            return FlowView::fromFlow($flow);
        }, $this->flowRepository->findByUser($user));
    }

    /**
     * Get a flow.
     *
     * @Route("/flows/{uuid}", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @View
     */
    public function getAction(Flow $flow)
    {
        return FlowView::fromFlow($flow);
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
        $this->flowRepository->remove($flow);
    }
}
