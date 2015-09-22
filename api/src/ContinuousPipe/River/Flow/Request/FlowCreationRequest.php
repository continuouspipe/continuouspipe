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
    private $ymlConfiguration;

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
    public function getYmlConfiguration()
    {
        return $this->ymlConfiguration;
    }
}
