<?php

namespace ContinuousPipe\River\Flow\Request;

use JMS\Serializer\Annotation as JMS;

class FlowCreationRequest
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $repository;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @deprecated Should be removed, as using only by `FlowController:deprecatedFromRepositoryAction`
     *
     * @var string
     */
    private $team;

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @deprecated
     *
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }
}
