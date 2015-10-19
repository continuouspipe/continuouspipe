<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Event\BeforeFlowSave;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\Flow as FlowView;
use ContinuousPipe\River\FlowFactory;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Team\Team;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param FlowRepository     $flowRepository
     * @param FlowFactory        $flowFactory
     * @param MessageBus         $eventBus
     * @param TideRepository     $tideRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(FlowRepository $flowRepository, FlowFactory $flowFactory, MessageBus $eventBus, TideRepository $tideRepository, ValidatorInterface $validator)
    {
        $this->flowRepository = $flowRepository;
        $this->eventBus = $eventBus;
        $this->flowFactory = $flowFactory;
        $this->tideRepository = $tideRepository;
        $this->validator = $validator;
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
        $errors = $this->validator->validate($creationRequest);
        if ($errors->count() > 0) {
            return \FOS\RestBundle\View\View::create($errors->get(0), 400);
        }

        $flow = $this->flowFactory->fromCreationRequest($creationRequest);
        $this->eventBus->handle(new BeforeFlowSave($flow));
        $flow = $this->flowRepository->save($flow);

        return FlowView::fromFlow($flow);
    }

    /**
     * Create a new flow from a repository.
     *
     * @Route("/teams/{slug}/flows", methods={"GET"})
     * @ParamConverter("team", converter="team", options={"slug"="slug"})
     * @View
     */
    public function listAction(Team $team)
    {
        return array_map(function (Flow $flow) {
            $lastTides = $this->tideRepository->findLastByFlow($flow, 1);

            return FlowView::fromFlowAndTides($flow, $lastTides);
        }, $this->flowRepository->findByTeam($team));
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
