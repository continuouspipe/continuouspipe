<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Flow\Request\FlowCreationRequest;
use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use ContinuousPipe\User\Security\UserContext;
use Rhumsaa\Uuid\Uuid;

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

    public function __construct(UserContext $userContext, CodeRepositoryRepository $codeRepositoryRepository)
    {
        $this->userContext = $userContext;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
    }

    public function fromCreationRequest(FlowCreationRequest $creationRequest)
    {
        $flowContext = FlowContext::createFlow(
            Uuid::uuid1(),
            $this->userContext->getCurrent(),
            $this->codeRepositoryRepository->findByIdentifier($creationRequest->getRepository())
        );

        return new Flow($flowContext, $creationRequest->getTasks());
    }
}
