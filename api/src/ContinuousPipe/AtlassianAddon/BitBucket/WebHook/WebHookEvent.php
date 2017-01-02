<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use ContinuousPipe\AtlassianAddon\BitBucket\Actor;
use ContinuousPipe\AtlassianAddon\BitBucket\Repository;
use JMS\Serializer\Annotation as JMS;

abstract class WebHookEvent
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Actor")
     *
     * @var Actor
     */
    private $actor;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Repository")
     *
     * @var Repository
     */
    private $repository;

    /**
     * @return Actor
     */
    public function getActor(): Actor
    {
        return $this->actor;
    }

    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return $this->repository;
    }
}
