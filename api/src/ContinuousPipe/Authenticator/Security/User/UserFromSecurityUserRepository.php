<?php

namespace ContinuousPipe\Authenticator\Security\User;

use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Security\User\UserRepository;

class UserFromSecurityUserRepository implements UserRepository
{
    /**
     * @var SecurityUserRepository
     */
    private $repository;

    public function __construct(SecurityUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUsername($username)
    {
        return $this->repository->findOneByUsername($username)->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function save(User $user)
    {
        $this->repository->save(new SecurityUser(
            $user
        ));
    }
}
