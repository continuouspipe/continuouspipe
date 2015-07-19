<?php

namespace ContinuousPipe\River\Command\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\User\User;

class SetupWebHookCommand
{
    /**
     * @var CodeRepository
     */
    private $repository;
    /**
     * @var User
     */
    private $user;

    /**
     * @param CodeRepository $repository
     * @param User           $user
     */
    public function __construct(CodeRepository $repository, User $user)
    {
        $this->repository = $repository;
        $this->user = $user;
    }

    /**
     * @return CodeRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
