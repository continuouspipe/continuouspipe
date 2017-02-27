<?php

namespace ContinuousPipe\DevelopmentEnvironment\InitializationToken;

use Ramsey\Uuid\UuidInterface;

class InitializationToken
{
    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var UuidInterface
     */
    private $developmentEnvironmentUuid;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $gitBranch;

    public function __construct(UuidInterface $flowUuid, UuidInterface $developmentEnvironmentUuid, string $apiKey, string $username, string $gitBranch)
    {
        $this->flowUuid = $flowUuid;
        $this->developmentEnvironmentUuid = $developmentEnvironmentUuid;
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->gitBranch = $gitBranch;
    }

    public function toString() : string
    {
        return base64_encode(implode(',', [
            $this->apiKey,
            $this->developmentEnvironmentUuid->toString(),
            $this->flowUuid->toString(),
            $this->username,
            $this->gitBranch
        ]));
    }
}
