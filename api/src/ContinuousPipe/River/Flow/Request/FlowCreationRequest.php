<?php

namespace ContinuousPipe\River\Flow\Request;

use JMS\Serializer\Annotation as JMS;

class FlowCreationRequest extends FlowUpdateRequest
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
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }
}
