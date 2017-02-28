<?php

namespace ContinuousPipe\DevelopmentEnvironment\InitializationToken;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class InitializationToken
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $developmentEnvironmentUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $apiKey;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     *
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

    /**
     * @return string
     */
    public function getGitBranch(): string
    {
        return $this->gitBranch;
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
