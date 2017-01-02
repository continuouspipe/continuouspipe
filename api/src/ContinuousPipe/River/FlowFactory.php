<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Flow\Request\FlowCreationRequest;
use ContinuousPipe\River\Flow\Request\FlowUpdateRequest;
use ContinuousPipe\Security\Authenticator\UserContext;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Yaml\Yaml;

class FlowFactory
{
    /**
     * @var UserContext
     */
    private $userContext;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @param UserContext        $userContext
     * @param MessageBus         $eventBus
     * @param FlatFlowRepository $flatFlowRepository
     */
    public function __construct(UserContext $userContext, MessageBus $eventBus, FlatFlowRepository $flatFlowRepository)
    {
        $this->userContext = $userContext;
        $this->eventBus = $eventBus;
        $this->flatFlowRepository = $flatFlowRepository;
    }

    /**
     * @param FlowCreationRequest $creationRequest
     *
     * @return FlatFlow
     */
    public function fromCreationRequest(Team $team, FlowCreationRequest $creationRequest)
    {
        if (null != $creationRequest->getUuid()) {
            $uuid = Uuid::fromString($creationRequest->getUuid());
        } else {
            $uuid = Uuid::uuid1();
        }

        $flow = Flow::create(
            $uuid,
            $team,
            $this->userContext->getCurrent(),
            $creationRequest->getRepository()
        );

        foreach ($flow->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $this->flatFlowRepository->find($uuid);
    }

    /**
     * @param Flow              $flow
     * @param FlowUpdateRequest $updateRequest
     *
     * @return FlatFlow
     */
    public function update(Flow $flow, FlowUpdateRequest $updateRequest)
    {
        $flow->update(
            $this->parseConfiguration($updateRequest)
        );

        foreach ($flow->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $this->flatFlowRepository->find($flow->getUuid());
    }

    /**
     * @param FlowUpdateRequest $updateRequest
     *
     * @return array
     */
    private function parseConfiguration(FlowUpdateRequest $updateRequest)
    {
        $configuration = $updateRequest->getYmlConfiguration();
        if (empty($configuration)) {
            return [];
        }

        return Yaml::parse($configuration);
    }
}
