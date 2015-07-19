<?php

namespace ContinuousPipe\River\Event;


use ContinuousPipe\Builder\Repository;
use Rhumsaa\Uuid\Uuid;

class TideStarted implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Uuid $tideUuid
     * @param Repository $repository
     */
    public function __construct(Uuid $tideUuid, Repository $repository)
    {
        $this->tideUuid = $tideUuid;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
