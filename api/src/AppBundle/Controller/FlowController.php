<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Event\BeforeFlowSave;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\Flow as FlowView;
use ContinuousPipe\River\FlowFactory;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View\TideRepository;
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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param FlowRepository $flowRepository
     * @param FlowFactory    $flowFactory
     * @param MessageBus     $eventBus
     * @param TideRepository $tideRepository
     */
    public function __construct(FlowRepository $flowRepository, FlowFactory $flowFactory, MessageBus $eventBus, TideRepository $tideRepository)
    {
        $this->flowRepository = $flowRepository;
        $this->eventBus = $eventBus;
        $this->flowFactory = $flowFactory;
        $this->tideRepository = $tideRepository;
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
        $this->eventBus->handle(new BeforeFlowSave($flow));
        $flow = $this->flowRepository->save($flow);

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
            $lastTides = $this->tideRepository->findLastByFlow($flow, 1);

            return FlowView::fromFlowAndTides($flow, $lastTides);
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
     * Update a flow.
     *
     * @Route("/flows/{uuid}", methods={"PUT"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("updateRequest", converter="fos_rest.request_body")
     * @View
     */
    public function updateAction(Flow $flow, Flow\Request\FlowUpdateRequest $updateRequest)
    {
        $flow = $this->flowFactory->fromUpdateRequest($flow, $updateRequest);
        $this->flowRepository->save($flow);

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
