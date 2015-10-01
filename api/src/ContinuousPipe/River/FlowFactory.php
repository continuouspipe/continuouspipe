<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Flow\Request\FlowCreationRequest;
use ContinuousPipe\River\Flow\Request\FlowUpdateRequest;
use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use ContinuousPipe\User\Security\UserContext;
use Rhumsaa\Uuid\Uuid;
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
     * @param UserContext              $userContext
     * @param CodeRepositoryRepository $codeRepositoryRepository
     */
    public function __construct(UserContext $userContext, CodeRepositoryRepository $codeRepositoryRepository)
    {
        $this->userContext = $userContext;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
    }

    /**
     * @param FlowCreationRequest $creationRequest
     *
     * @return Flow
     */
    public function fromCreationRequest(FlowCreationRequest $creationRequest)
    {
        if (null != $creationRequest->getUuid()) {
            $uuid = Uuid::fromString($creationRequest->getUuid());
        } else {
            $uuid = Uuid::uuid1();
        }

        $flowContext = FlowContext::createFlow(
            $uuid,
            $this->userContext->getCurrent(),
            $this->codeRepositoryRepository->findByIdentifier($creationRequest->getRepository()),
            $this->parseConfiguration($creationRequest)
        );

        return new Flow($flowContext);
    }

    /**
     * @param Flow              $flow
     * @param FlowUpdateRequest $updateRequest
     *
     * @return Flow
     */
    public function fromUpdateRequest(Flow $flow, FlowUpdateRequest $updateRequest)
    {
        $context = $flow->getContext();

        return new Flow(FlowContext::createFlow(
            $flow->getUuid(),
            $context->getUser(),
            $context->getCodeRepository(),
            $this->parseConfiguration($updateRequest)
        ));
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
