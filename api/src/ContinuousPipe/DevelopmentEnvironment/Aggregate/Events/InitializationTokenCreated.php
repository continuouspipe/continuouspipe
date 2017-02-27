<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate\Events;

use ContinuousPipe\DevelopmentEnvironment\InitializationToken\InitializationToken;
use Ramsey\Uuid\UuidInterface;

class InitializationTokenCreated extends DevelopmentEnvironmentEvent
{
    /**
     * @var InitializationToken
     */
    private $initializationToken;

    /**
     * @param UuidInterface $developmentEnvironmentUuid
     * @param InitializationToken $initializationToken
     */
    public function __construct(UuidInterface $developmentEnvironmentUuid, InitializationToken $initializationToken)
    {
        parent::__construct($developmentEnvironmentUuid);

        $this->initializationToken = $initializationToken;
    }

    /**
     * @return InitializationToken
     */
    public function getInitializationToken(): InitializationToken
    {
        return $this->initializationToken;
    }
}
