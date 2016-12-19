<?php

namespace ContinuousPipe\River\Pipeline;

use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\Security\User\User;

final class TideGenerationTrigger
{
    private $codeRepositoryEvent;
    private $user;

    public function __construct(CodeRepositoryEvent $codeRepositoryEvent = null, User $user = null)
    {
        $this->codeRepositoryEvent = $codeRepositoryEvent;
        $this->user = $user;
    }

    public static function user(User $user) : self
    {
        return new self(null, $user);
    }

    public static function codeRepositoryEvent(CodeRepositoryEvent $event) : self
    {
        return new self($event);
    }

    /**
     * @return CodeRepositoryEvent|null
     */
    public function getCodeRepositoryEvent()
    {
        return $this->codeRepositoryEvent;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
