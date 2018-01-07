<?php

namespace ContinuousPipe\Pipe\Client;

/**
 * @deprecated Duplicate of the `ContinuousPipe\Pipe\View\ComponentStatus` object, after merging pipe.
 *             Kept to be compatible with serialized tides.
 */
class ComponentStatus extends \ContinuousPipe\Pipe\View\ComponentStatus
{
    private $created;
    private $updated;
    private $deleted;

    public function isCreated()
    {
        return $this->created ?? parent::isCreated();
    }

    public function isUpdated()
    {
        return $this->updated ?? parent::isUpdated();
    }

    public function isDeleted()
    {
        return $this->deleted ?? parent::isDeleted();
    }
}
