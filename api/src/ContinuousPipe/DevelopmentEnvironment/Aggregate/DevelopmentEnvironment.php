<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate;

use ContinuousPipe\DevelopmentEnvironment\Aggregate\Events\DevelopmentEnvironmentCreated;
use ContinuousPipe\DevelopmentEnvironment\Aggregate\Events\InitializationTokenCreated;
use ContinuousPipe\DevelopmentEnvironment\InitializationToken\InitializationToken;
use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironment as ReadModelDevelopmentEnvironment;
use ContinuousPipe\DevelopmentEnvironmentBundle\Request\InitializationTokenCreationRequest;
use ContinuousPipe\River\EventBased\ApplyEventCapability;
use ContinuousPipe\River\EventBased\RaiseEventCapability;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class DevelopmentEnvironment
{
    use ApplyEventCapability, RaiseEventCapability;

    private $uuid;
    private $flowUuid;
    private $username;
    private $name;
    private $modificationDate;
    private $initializationToken;

    public static function create(UuidInterface $flowUuid, User $user, string $name) : DevelopmentEnvironment
    {
        $developmentEnvironment = new self();
        $developmentEnvironment->raiseAndApply(
            new DevelopmentEnvironmentCreated(
                Uuid::uuid4(),
                $flowUuid,
                $user->getUsername(),
                $name,
                new \DateTime()
            )
        );

        return $developmentEnvironment;
    }

    public function createInitializationToken(AuthenticatorClient $authenticatorClient, User $user, InitializationTokenCreationRequest $request)
    {
        $apiKey = $authenticatorClient->createApiKey($user, 'API key for remote environment "'.$this->name.'""');
        $token = new InitializationToken(
            $this->flowUuid,
            $this->uuid,
            $apiKey->getApiKey(),
            $user->getUsername(),
            $request->getGitBranch()
        );

        $this->raiseAndApply(new InitializationTokenCreated(
            $this->uuid,
            $token
        ));
    }

    public function applyDevelopmentEnvironmentCreated(DevelopmentEnvironmentCreated $event)
    {
        $this->uuid = $event->getDevelopmentEnvironmentUuid();
        $this->flowUuid = $event->getFlowUuid();
        $this->username = $event->getUsername();
        $this->name = $event->getName();
        $this->modificationDate = $event->getDateTime();
    }

    public function applyInitializationTokenCreated(InitializationTokenCreated $event)
    {
        $this->initializationToken = $event->getInitializationToken();
    }

    public function getUuid() : UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return InitializationToken|null
     */
    public function getInitializationToken()
    {
        return $this->initializationToken;
    }

    private function raiseAndApply($event)
    {
        $this->raise($event);
        $this->apply($event);
    }

    public function createView() : ReadModelDevelopmentEnvironment
    {
        return new ReadModelDevelopmentEnvironment(
            $this->uuid,
            $this->flowUuid,
            $this->username,
            $this->name,
            $this->modificationDate
        );
    }
}
