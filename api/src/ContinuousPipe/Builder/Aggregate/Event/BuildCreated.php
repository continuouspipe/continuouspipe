<?php

namespace ContinuousPipe\Builder\Aggregate\Event;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class BuildCreated extends BuildEvent
{
    /**
     * @var BuildRequest
     */
    private $request;

    /**
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
