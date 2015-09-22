<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Flow\Request\FlowCreationRequest;
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
        $flowContext = FlowContext::createFlow(
            Uuid::uuid1(),
            $this->userContext->getCurrent(),
            $this->codeRepositoryRepository->findByIdentifier($creationRequest->getRepository()),
            $this->parseConfiguration($creationRequest)
        );

        return new Flow($flowContext);
    }

    /**
     * @param FlowCreationRequest $creationRequest
     *
     * @return array
     */
    private function parseConfiguration(FlowCreationRequest $creationRequest)
    {
        $configuration = $creationRequest->getYmlConfiguration();
        if (empty($configuration)) {
            return [];
        }

        return Yaml::parse($configuration);
    }
}
