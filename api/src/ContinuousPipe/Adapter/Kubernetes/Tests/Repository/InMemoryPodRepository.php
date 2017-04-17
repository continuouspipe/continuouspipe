<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use GuzzleHttp\Promise\PromiseInterface;
use Kubernetes\Client\Exception\PodNotFound;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodList;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Repository\PodRepository;

class InMemoryPodRepository implements PodRepository
{
    /**
     * @var Pod[]
     */
    private $pods = [];

    /**
     * @var callable
     */
    private $attachCallback;

    /**
     * @return PodList
     */
    public function findAll()
    {
        return PodList::fromPods($this->pods);
    }

    /**
     * {@inheritdoc}
     */
    public function asyncFindAll() : PromiseInterface
    {
        return \GuzzleHttp\Promise\promise_for($this->findAll());
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        $pods = array_values(array_filter($this->pods, function (Pod $pod) use ($labels) {
            $podLabels = $pod->getMetadata()->getLabelsAsAssociativeArray();

            foreach ($labels as $key => $value) {
                if (!array_key_exists($key, $podLabels) || $podLabels[$key] != $value) {
                    return false;
                }
            }

            return true;
        }));

        return PodList::fromPods($pods);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Pod $pod)
    {
        $this->pods[$pod->getMetadata()->getName()] = $pod;

        return $pod;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Pod $pod)
    {
        $this->pods[$pod->getMetadata()->getName()] = $pod;

        return $pod;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->pods)) {
            throw new PodNotFound();
        }

        return $this->pods[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Pod $pod)
    {
        $name = $pod->getMetadata()->getName();
        if (!array_key_exists($name, $this->pods)) {
            throw new PodNotFound();
        }

        unset($this->pods[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByReplicationController(ReplicationController $replicationController)
    {
        return $this->findByLabels($replicationController->getSpecification()->getSelector());
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->pods);
    }

    /**
     * {@inheritdoc}
     */
    public function attach(Pod $pod, callable $callable)
    {
        if (null === $this->attachCallback) {
            throw new \RuntimeException('No attach callback registered');
        }

        $callback = $this->attachCallback;

        return $callback($pod, $callable);
    }

    /**
     * @param callable $attachCallback
     */
    public function setAttachCallback(callable $attachCallback)
    {
        $this->attachCallback = $attachCallback;
    }
}
