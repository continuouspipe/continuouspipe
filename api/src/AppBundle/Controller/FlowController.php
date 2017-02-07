<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeReference;
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
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var Flow\MissingVariables\MissingVariableResolver
     */
    private $missingVariableResolver;

    /**
     * @param Flow\Projections\FlatFlowRepository           $flowRepository
     * @param FlowFactory                                   $flowFactory
     * @param MessageBus                                    $eventBus
     * @param TideRepository                                $tideRepository
     * @param ValidatorInterface                            $validator
     * @param TeamRepository                                $teamRepository
     * @param Flow\MissingVariables\MissingVariableResolver $missingVariableResolver
     */
    public function __construct(
        Flow\Projections\FlatFlowRepository $flowRepository,
        FlowFactory $flowFactory,
        MessageBus $eventBus,
        TideRepository $tideRepository,
        ValidatorInterface $validator,
        TeamRepository $teamRepository,
        Flow\MissingVariables\MissingVariableResolver $missingVariableResolver
    ) {
        $this->flowRepository = $flowRepository;
        $this->eventBus = $eventBus;
        $this->flowFactory = $flowFactory;
        $this->tideRepository = $tideRepository;
        $this->validator = $validator;
        $this->teamRepository = $teamRepository;
        $this->missingVariableResolver = $missingVariableResolver;
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

        return $this->flowFactory->fromCreationRequest($team, $creationRequest);
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
     * Get a flow configuration.
     *
     * @Route("/flows/{uuid}/configuration", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function getConfigurationAction(FlatFlow $flow)
    {
        $defaultCodeReference = CodeReference::repositoryDefault($flow->getRepository());

        return [
            'configuration' => $flow->getConfiguration(),
            'missing_variables' => $this->missingVariableResolver->findMissingVariables($flow, $defaultCodeReference),
        ];
    }

    /**
     * Update the flow configuration.
     *
     * @Route("/flows/{uuid}/configuration", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("updateRequest", converter="fos_rest.request_body")
     * @Security("is_granted('UPDATE', flow)")
     * @View
     */
    public function updateAction(Flow $flow, Flow\Request\FlowUpdateRequest $updateRequest)
    {
        try {
            return $this->flowFactory->update($flow, $updateRequest);
        } catch (Flow\ConfigurationException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
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
