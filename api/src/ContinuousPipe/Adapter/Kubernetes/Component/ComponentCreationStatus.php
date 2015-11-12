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
        parent::__construct(count($created) > 0, count($updated) > 0, count($deleted) > 0);

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
}
