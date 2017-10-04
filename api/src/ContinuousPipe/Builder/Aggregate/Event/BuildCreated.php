<?php

namespace ContinuousPipe\Builder\Aggregate\Event;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class BuildCreated extends BuildEvent
{
    /**
     * @JMS\Type("ContinuousPipe\Builder\Request\BuildRequest")
     *
     * @var BuildRequest
     */
    private $request;

    /**
     * @param string $buildIdentifier
     * @param BuildRequest $request
     */
    public function __construct(string $buildIdentifier, BuildRequest $request)
    {
        parent::__construct($buildIdentifier);

        $this->request = $request;
    }

    /**
     * @return BuildRequest
     */
    public function getRequest(): BuildRequest
    {
        return $this->request;
    }
}
