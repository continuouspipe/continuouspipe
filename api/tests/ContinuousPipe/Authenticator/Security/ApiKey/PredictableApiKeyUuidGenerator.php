<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use Ramsey\Uuid\UuidInterface;

class PredictableApiKeyUuidGenerator implements ApiKeyUuidGenerator
{
    /**
     * @var ApiKeyUuidGenerator
     */
    private $generator;

    /**
     * @var UuidInterface|null
     */
    private $uuidToGenerate;

    public function __construct(ApiKeyUuidGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate(): UuidInterface
    {
        if (null !== $this->uuidToGenerate) {
            return $this->uuidToGenerate;
        }

        return $this->generator->generate();
    }

    /**
     * @param null|UuidInterface $uuidToGenerate
     */
    public function setUuidToGenerate($uuidToGenerate)
    {
        $this->uuidToGenerate = $uuidToGenerate;
    }
}
