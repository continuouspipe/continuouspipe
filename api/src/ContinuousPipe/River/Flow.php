<?php

namespace ContinuousPipe\River;

use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;

class Flow
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var User
     */
    private $user;

    /**
     * @var CodeRepository
     */
    private $repository;

    /**
     * @param Uuid $uuid
     * @param User $user
     * @param CodeRepository $repository
     */
    public function __construct(Uuid $uuid, User $user, CodeRepository $repository)
    {
        $this->uuid = $uuid;
        $this->user = $user;
        $this->repository = $repository;
    }

    /**
     * @param User $user
     * @param CodeRepository $repository
     * @return Flow
     */
    public static function fromUserAndCodeRepository(User $user, CodeRepository $repository)
    {
        return new self(Uuid::uuid1(), $user, $repository);
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return CodeRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
