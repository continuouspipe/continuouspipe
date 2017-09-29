<?php

namespace ContinuousPipe\Builder\Request;

use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Logging;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Repository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\LogStreamLogging;

class BuildRequest
{
    /**
     * @var Logging
     */
    private $logging;

    /**
     * @var UuidInterface
     */
    private $credentialsBucket;

    /**
     * @var BuildStepConfiguration[]
     */
    private $steps;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @deprecated Should use the `steps` instead.
     *
     * @var Repository|null
     */
    private $repository;

    /**
     * @deprecated Should use the `steps` instead.
     *
     * @var ArchiveSource|null
     */
    private $archive;

    /**
     * @deprecated Should use the `steps` instead.
     *
     * @var Image|null
     */
    private $image;

    /**
     * @deprecated Should use the `steps` instead.
     *
     * @var Context|null
     */
    private $context;

    /**
     * @deprecated Should use the `steps` instead.
     *
     * @var array|null
     */
    private $environment;

    /**
     * @param BuildStepConfiguration[] $steps
     * @param Logging            $logging
     * @param UuidInterface      $credentialsBucket
     */
    public function __construct(array $steps, Logging $logging, UuidInterface $credentialsBucket)
    {
        $this->steps = $steps;
        $this->logging = $logging;
        $this->credentialsBucket = $credentialsBucket;
    }

    /**
     * @return Logging
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @return UuidInterface
     */
    public function getCredentialsBucket()
    {
        if (is_string($this->credentialsBucket)) {
            $this->credentialsBucket = Uuid::fromString($this->credentialsBucket);
        }

        return $this->credentialsBucket;
    }

    /**
     * @return Engine|null
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @return BuildStepConfiguration[]
     */
    public function getSteps()
    {
        return $this->steps ?: [];
    }

    public function withSteps(array $steps) : BuildRequest
    {
        $request = clone $this;
        $request->steps = $steps;

        return $request;
    }

    /**
     * @deprecated Should be using the build steps instead.
     *
     * @return Repository|null
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @deprecated Should be using the build steps instead.
     *
     * @return ArchiveSource|null
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @deprecated Should be using the build steps instead.
     *
     * @return Image|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @deprecated Should be using the build steps instead.
     *
     * @return Context|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @deprecated Should be using the build steps instead.
     *
     * @return array|null
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function withParentLogIdentifier(string $parentLogIdentifier) : BuildRequest
    {
        $request = clone $this;
        $request->logging = Logging::withLogStream(LogStreamLogging::fromParentLogIdentifier($parentLogIdentifier));

        return $request;
    }

    public function withEngine(Engine $engine)
    {
        $request = clone $this;
        $request->engine = $engine;

        return $request;
    }
}
