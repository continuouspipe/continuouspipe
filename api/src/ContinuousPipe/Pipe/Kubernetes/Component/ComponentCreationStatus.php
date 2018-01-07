<?php

namespace ContinuousPipe\Pipe\Kubernetes\Component;

use ContinuousPipe\Pipe\View\ComponentStatus;
use Kubernetes\Client\Model\KubernetesObject;

class ComponentCreationStatus extends ComponentStatus
{
    /**
     * @var KubernetesObject[]
     */
    private $created;

    /**
     * @var KubernetesObject[]
     */
    private $updated;

    /**
     * @var KubernetesObject[]
     */
    private $deleted;

    /**
     * @var KubernetesObject[]
     */
    private $ignored;

    /**
     * @param KubernetesObject[] $created
     * @param KubernetesObject[] $updated
     * @param KubernetesObject[] $deleted
     * @param KubernetesObject[] $ignored
     */
    public function __construct(array $created = [], array $updated = [], array $deleted = [], array $ignored = [])
    {
        $this->created = $created;
        $this->updated = $updated;
        $this->deleted = $deleted;
        $this->ignored = $ignored;
    }

    /**
     * @param KubernetesObject $object
     */
    public function addCreated(KubernetesObject $object)
    {
        $this->created[] = $object;
    }

    /**
     * @param KubernetesObject $object
     */
    public function addUpdated(KubernetesObject $object)
    {
        $this->updated[] = $object;
    }

    /**
     * @param KubernetesObject $object
     */
    public function addDeleted(KubernetesObject $object)
    {
        $this->deleted = $object;
    }

    /**
     * @return \Kubernetes\Client\Model\KubernetesObject[]
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \Kubernetes\Client\Model\KubernetesObject[]
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return \Kubernetes\Client\Model\KubernetesObject[]
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return \Kubernetes\Client\Model\KubernetesObject[]
     */
    public function getIgnored()
    {
        return $this->ignored;
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return count($this->created) > 0;
    }

    /**
     * @return bool
     */
    public function isUpdated()
    {
        return count($this->updated) > 0;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return count($this->deleted) > 0;
    }

    /**
     * @param ComponentCreationStatus $status
     */
    public function merge(ComponentCreationStatus $status)
    {
        $this->created = array_merge($this->created, $status->getCreated());
        $this->updated = array_merge($this->updated, $status->getUpdated());
        $this->deleted = array_merge($this->deleted, $status->getDeleted());
        $this->ignored = array_merge($this->ignored, $status->getIgnored());
    }
}
