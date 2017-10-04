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
     * @JMS\Type("ContinuousPipe\Security\User\User")
     *
     * @var User
     */
    private $user;

    /**
     * @param string $buildIdentifier
     * @param BuildRequest $request
     * @param User $user
     */
    public function __construct(string $buildIdentifier, BuildRequest $request, User $user)
    {
        parent::__construct($buildIdentifier);

        $this->request = $request;
        $this->user = $user;
    }

    /**
     * @return BuildRequest
     */
    public function getRequest(): BuildRequest
    {
        return $this->request;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
