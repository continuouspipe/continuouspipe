<?php

namespace ContinuousPipe\DevelopmentEnvironment\InitializationToken;

use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKeyFactory;
use ContinuousPipe\DevelopmentEnvironment\Aggregate\DevelopmentEnvironment;
use ContinuousPipe\DevelopmentEnvironment\Aggregate\DevelopmentEnvironmentRepository;
use ContinuousPipe\DevelopmentEnvironmentBundle\Request\InitializationTokenCreationRequest;
use ContinuousPipe\Events\Transaction\TransactionManager;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\MessageBus;

class InitializationTokenFactory
{
    /**
     * @var DevelopmentEnvironmentRepository
     */
    private $developmentEnvironmentRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var UserApiKeyFactory
     */
    private $userApiKeyFactory;

    public function __construct(
        DevelopmentEnvironmentRepository $developmentEnvironmentRepository,
        MessageBus $eventBus,
        UserApiKeyFactory $userApiKeyFactory
    ) {
        $this->developmentEnvironmentRepository = $developmentEnvironmentRepository;
        $this->eventBus = $eventBus;
        $this->userApiKeyFactory = $userApiKeyFactory;
    }

    public function create(UuidInterface $developmentEnvironmentUuid, User $user, InitializationTokenCreationRequest $creationRequest) : InitializationToken
    {
        $developmentEnvironment = $this->developmentEnvironmentRepository->find($developmentEnvironmentUuid);
        $developmentEnvironment->createInitializationToken($this->userApiKeyFactory, $user, $creationRequest);

        foreach ($developmentEnvironment->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $developmentEnvironment->getInitializationToken();
    }
}
