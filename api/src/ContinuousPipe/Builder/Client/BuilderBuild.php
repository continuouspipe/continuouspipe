<?php

namespace ContinuousPipe\Builder\Client;

use JMS\Serializer\Annotation as JMS;

class BuilderBuild
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

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
    private $status;

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getStatus() == self::STATUS_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isErrored()
    {
        return $this->getStatus() == self::STATUS_ERROR;
    }
}
