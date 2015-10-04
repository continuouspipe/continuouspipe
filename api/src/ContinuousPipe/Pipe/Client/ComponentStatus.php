<?php

namespace ContinuousPipe\Pipe\Client;

use JMS\Serializer\Annotation as JMS;

class ComponentStatus
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $created;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $updated;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $deleted;

    /**
     * @param bool $created
     * @param bool $updated
     * @param bool $deleted
     */
    public function __construct($created, $updated, $deleted)
    {
        $this->created = $created;
        $this->updated = $updated;
        $this->deleted = $deleted;
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->created;
    }

    /**
     * @return bool
     */
    public function isUpdated()
    {
        return $this->updated;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }
}
