<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector\ReverseTransformer;

use ContinuousPipe\Adapter\Kubernetes\Inspector\NamespaceSnapshot;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Component\ResourcesRequest;
use ContinuousPipe\Model\Status;
use Kubernetes\Client\Model\Container;
use Kubernetes\Client\Model\ContainerStatus;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;

class ComponentTransformer
{
    /**
     * @var ComponentPublicEndpointResolver
     */
    private $resolver;

    /**
     * @param ComponentPublicEndpointResolver $resolver
     */
    public function __construct(ComponentPublicEndpointResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param NamespaceSnapshot $snapshot
     *
     * @return Component[]
     */
    public function componentsFromSnapshot(NamespaceSnapshot $snapshot) : array
    {
        return array_map(function (KubernetesObject $object) use ($snapshot) {
            return $this->componentFromObject($snapshot, $object);
        }, array_merge(
            $snapshot->getDeployments()->getDeployments(),
            $snapshot->getReplicationControllers()->getReplicationControllers()
        ));
    }

    /**
     * @param NamespaceSnapshot                $snapshot
     * @param ReplicationController|Deployment $object
     *
     * @return Component
     */
    private function componentFromObject(NamespaceSnapshot $snapshot, KubernetesObject $object) : Component
    {
        $name = $object->getMetadata()->getName();
        $replicas = $object->getSpecification()->getReplicas();

        $containers = $this->getContainers($object);
        if (0 === count($containers)) {
            throw new \InvalidArgumentException('No container found in deployment\'s specification');
        }

        return new Component(
            $name,
            $name,
            new Component\Specification(
                $this->getComponentSource($containers[0]),
                $this->getComponentAccessibility($snapshot, $name),
                new Component\Scalability(true, $replicas),
                [],
                [],
                [],
                [],
                null,
                null,
                $this->getComponentResources($containers[0])
            ),
            [],
            [],
            $this->getComponentStatus($snapshot, $object),
            new Component\DeploymentStrategy(null, null, false, false)
        );
    }

    private function getComponentResources(Container $container)
    {
        $resources = $container->getResources();

        if (is_null($resources)) {
            return null;
        }

        $requests = $resources->getRequests();
        $requests = new ResourcesRequest($requests->getCpu(), $requests->getMemory());

        $limits = $resources->getLimits();
        $limits = new ResourcesRequest($limits->getCpu(), $limits->getMemory());

        return new Component\Resources($requests, $limits);
    }

    /**
     * @param Container $container
     *
     * @return Component\Source
     */
    private function getComponentSource(Container $container)
    {
        $imageName = $container->getImage();
        $tagName = null;

        if (($semiColonPosition = strpos($imageName, ':')) !== false) {
            $imageName = substr($imageName, 0, $semiColonPosition);
            $tagName = substr($imageName, $semiColonPosition);
        }

        return new Component\Source($imageName, $tagName);
    }

    /**
     * @param NamespaceSnapshot $snapshot
     * @param string          $serviceName
     *
     * @return Component\Accessibility
     */
    private function getComponentAccessibility(NamespaceSnapshot $snapshot, $serviceName)
    {
        if ($service = $this->findOneByName($snapshot->getServices(), $serviceName)) {
            return new Component\Accessibility(
                true,
                $service->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER
            );
        } else {
            return new Component\Accessibility(false, false);
        }
    }

    /**
     * @param NamespaceSnapshot $snapshot
     * @param KubernetesObject  $object
     *
     * @return Component\Status
     */
    private function getComponentStatus(NamespaceSnapshot $snapshot, KubernetesObject $object)
    {
        if ($object instanceof ReplicationController) {
            $labels = $object->getSpecification()->getSelector();
        } elseif ($object instanceof Deployment) {
            $labels = $object->getSpecification()->getSelector()->getMatchLabels();
        } else {
            throw new \InvalidArgumentException(sprintf('Unable to get status from object %s', get_class($object)));
        }

        $pods = $this->findByLabels($snapshot->getPods(), $labels);
        $componentName = $object->getMetadata()->getName();
        $replicas = $object->getSpecification()->getReplicas();
        $healthyPods = $this->filterHealthyPods($pods);

        if (count($healthyPods) == $replicas) {
            $status = Component\Status::HEALTHY;
        } elseif (count($healthyPods) > 0) {
            $status = Component\Status::WARNING;
        } else {
            $status = Component\Status::UNHEALTHY;
        }

        return new Component\Status(
            $status,
            $this->getComponentPublicEndpoints($snapshot, $componentName),
            $this->getContainerStatuses($pods)
        );
    }

    /**
     * @param NamespaceSnapshot $snapshot
     * @param string          $componentName
     *
     * @return array
     */
    private function getComponentPublicEndpoints(NamespaceSnapshot $snapshot, $componentName)
    {
        $publicEndpoints = [];

        foreach ($this->getServicesAndIngresses($snapshot, $componentName) as $serviceOrIngress) {
            $publicEndpoints = array_merge($publicEndpoints, $this->resolver->resolve($serviceOrIngress));
        }

        return $publicEndpoints;
    }

    /**
     * @param Pod[] $pods
     *
     * @return Pod[]
     */
    private function filterHealthyPods(array $pods)
    {
        return array_filter($pods, function (Pod $pod) {
            return $this->isPodHealthy($pod);
        });
    }

    /**
     * @param Pod[] $pods
     *
     * @return Component\Status\ContainerStatus[]
     */
    private function getContainerStatuses(array $pods)
    {
        return array_map(function (Pod $pod) {
            $status = $pod->getStatus();

            return new Component\Status\ContainerStatus(
                $pod->getMetadata()->getName(),
                $this->isPodHealthy($pod) ? Status::HEALTHY : Status::UNHEALTHY,
                array_reduce($status->getContainerStatuses() ?: [], function ($count, ContainerStatus $status) {
                    return $count + $status->getRestartCount();
                }, 0)
            );
        }, $pods);
    }

    /**
     * @param Pod $pod
     *
     * @return bool
     */
    private function isPodHealthy(Pod $pod)
    {
        if (null === ($status = $pod->getStatus())) {
            return false;
        } elseif (count($status->getContainerStatuses()) === 0) {
            return false;
        }

        return array_reduce($status->getContainerStatuses(), function ($ready, ContainerStatus $containerStatus) {
            return $ready && $containerStatus->isReady();
        }, true);
    }

    /**
     * @param NamespaceSnapshot $snapshot
     * @param string            $componentName
     *
     * @return Service[]|Ingress[]|null[]
     */
    private function getServicesAndIngresses(NamespaceSnapshot $snapshot, $componentName)
    {
        $labels = [
            'component-identifier' => $componentName,
        ];

        return array_merge(
            $this->findByLabels($snapshot->getServices(), $labels),
            $this->findByLabels($snapshot->getIngresses(), $labels)
        );
    }

    /**
     * @param KubernetesObject[]|\Traversable $list
     * @param string $name
     *
     * @return KubernetesObject|null
     */
    private function findOneByName(\Traversable $list, string $name)
    {
        foreach ($list as $object) {
            if ($object->getMetadata()->getName() == $name) {
                return $object;
            }
        }

        return null;
    }

    private function findByLabels(\Traversable $list, array $labels) : array
    {
        $matchingObjects = [];

        foreach ($list as $object) {
            if ($this->hasLabels($object, $labels)) {
                $matchingObjects[] = $object;
            }
        }

        return $matchingObjects;
    }

    private function hasLabels(KubernetesObject $object, array $labels) : bool
    {
        $objectLabels = $object->getMetadata()->getLabelsAsAssociativeArray();

        foreach ($labels as $key => $value) {
            if (!array_key_exists($key, $objectLabels) || $objectLabels[$key] != $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param KubernetesObject $object
     *
     * @return Container[]
     */
    private function getContainers(KubernetesObject $object) : array
    {
        if ($object instanceof ReplicationController) {
            return $object->getSpecification()->getPodTemplateSpecification()->getPodSpecification()->getContainers();
        } elseif ($object instanceof Deployment) {
            return $object->getSpecification()->getTemplate()->getPodSpecification()->getContainers();
        }

        return [];
    }
}
