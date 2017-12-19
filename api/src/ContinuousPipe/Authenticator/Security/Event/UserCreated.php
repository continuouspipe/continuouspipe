<?php

namespace ContinuousPipe\Authenticator\Security\Event;

use ContinuousPipe\Security\User\User;
use Symfony\Component\EventDispatcher\Event;

class UserCreated extends Event
{
    const EVENT_NAME = 'security.user_created';

    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
