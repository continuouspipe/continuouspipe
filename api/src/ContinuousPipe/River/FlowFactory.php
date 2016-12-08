<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\BeforeFlowSave;
use ContinuousPipe\River\Flow\Request\FlowCreationRequest;
use ContinuousPipe\River\Flow\Request\FlowUpdateRequest;
use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use ContinuousPipe\River\Repository\FlowRepository;
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
     * @var CodeRepositoryRepository
     */
    private $codeRepositoryRepository;
    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param UserContext              $userContext
     * @param CodeRepositoryRepository $codeRepositoryRepository
     * @param MessageBus               $eventBus
     * @param FlowRepository           $flowRepository
     */
    public function __construct(UserContext $userContext, CodeRepositoryRepository $codeRepositoryRepository, MessageBus $eventBus, FlowRepository $flowRepository)
    {
        $this->userContext = $userContext;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
        $this->eventBus = $eventBus;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @param FlowCreationRequest $creationRequest
     *
     * @return Flow
     */
    public function fromCreationRequest(Team $team, FlowCreationRequest $creationRequest)
    {
        if (null != $creationRequest->getUuid()) {
            $uuid = Uuid::fromString($creationRequest->getUuid());
        } else {
            $uuid = Uuid::uuid1();
        }

        $flowContext = FlowContext::createFlow(
            $uuid,
            $team,
            $this->userContext->getCurrent(),
            $this->codeRepositoryRepository->findByIdentifier($creationRequest->getRepository()),
            $this->parseConfiguration($creationRequest)
        );

        $flow = Flow::fromContext($flowContext);
        $this->eventBus->handle(new BeforeFlowSave($flow));
        $flow = $this->flowRepository->save($flow);

        return $flow;
    }

    /**
     * @param Flow              $flow
     * @param FlowUpdateRequest $updateRequest
     *
     * @return Flow
     */
    public function update(Flow $flow, FlowUpdateRequest $updateRequest)
    {
        $flow = Flow::fromContext(FlowContext::createFlow(
            $flow->getUuid(),
            $flow->getTeam(),
            $flow->getUser(),
            $flow->getCodeRepository(),
            $this->parseConfiguration($updateRequest)
        ));

        $this->flowRepository->save($flow);

        return $flow;
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
