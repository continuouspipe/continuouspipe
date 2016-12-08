<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\Projections\FlatFlow as FlowView;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\FlowFactory;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @var Flow\Projections\FlatFlowRepository
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
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @param Flow\Projections\FlatFlowRepository $flowRepository
     * @param FlowFactory                         $flowFactory
     * @param MessageBus                          $eventBus
     * @param TideRepository                      $tideRepository
     * @param ValidatorInterface                  $validator
     * @param TeamRepository                      $teamRepository
     */
    public function __construct(Flow\Projections\FlatFlowRepository $flowRepository, FlowFactory $flowFactory, MessageBus $eventBus, TideRepository $tideRepository, ValidatorInterface $validator, TeamRepository $teamRepository)
    {
        $this->flowRepository = $flowRepository;
        $this->eventBus = $eventBus;
        $this->flowFactory = $flowFactory;
        $this->tideRepository = $tideRepository;
        $this->validator = $validator;
        $this->teamRepository = $teamRepository;
    }

    /**
     * Create a flow in the team.
     *
     * @Route("/teams/{slug}/flows", methods={"POST"})
     * @ParamConverter("team", converter="team", options={"slug"="slug"})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @View
     */
    public function fromRepositoryAction(Team $team, Flow\Request\FlowCreationRequest $creationRequest)
    {
        $errors = $this->validator->validate($creationRequest);
        if ($errors->count() > 0) {
            return \FOS\RestBundle\View\View::create($errors->get(0), 400);
        }

        $flow = $this->flowFactory->fromCreationRequest($team, $creationRequest);

        return FlowView::fromFlow($flow);
    }

    /**
     * List flows of a team.
     *
     * @Route("/teams/{slug}/flows", methods={"GET"})
     * @ParamConverter("team", converter="team", options={"slug"="slug"})
     * @View
     */
    public function listAction(Team $team)
    {
        return array_map(function (FlatFlow $flow) {
            $lastTides = $this->tideRepository->findLastByFlowUuid($flow->getUuid(), 1);

            return FlowView::fromFlowAndTides($flow, $lastTides);
        }, $this->flowRepository->findByTeam($team));
    }

    /**
     * Get a flow.
     *
     * @Route("/flows/{uuid}", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function getAction(FlatFlow $flow)
    {
        return $flow;
    }

    /**
     * Update a flow.
     *
     * @Route("/flows/{uuid}", methods={"PUT"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("updateRequest", converter="fos_rest.request_body")
     * @Security("is_granted('UPDATE', flow)")
     * @View
     */
    public function updateAction(Flow $flow, Flow\Request\FlowUpdateRequest $updateRequest)
    {
        $flow = $this->flowFactory->update($flow, $updateRequest);

        return FlowView::fromFlow($flow);
    }

    /**
     * Delete a flow.
     *
     * @Route("/flows/{uuid}", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('DELETE', flow)")
     * @View
     */
    public function deleteAction(FlatFlow $flow)
    {
        $this->flowRepository->remove($flow->getUuid());
    }
}
