<?php

namespace ContinuousPipe\Adapter\Kubernetes\Component;

use ContinuousPipe\Pipe\View\ComponentStatus;
use Kubernetes\Client\Model\KubernetesObject;

class ComponentCreationStatus extends ComponentStatus
{
    /**
     * @var \Kubernetes\Client\Model\KubernetesObject[]
     */
    private $created;

    /**
     * @var \Kubernetes\Client\Model\KubernetesObject[]
     */
    private $updated;

    /**
     * @var \Kubernetes\Client\Model\KubernetesObject[]
     */
    private $deleted;

    /**
     * @param KubernetesObject[] $created
     * @param KubernetesObject[] $updated
     * @param KubernetesObject[] $deleted
     */
    public function __construct(array $created = [], array $updated = [], array $deleted = [])
    {
        $this->created = $created;
        $this->updated = $updated;
        $this->deleted = $deleted;
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
    }
}
