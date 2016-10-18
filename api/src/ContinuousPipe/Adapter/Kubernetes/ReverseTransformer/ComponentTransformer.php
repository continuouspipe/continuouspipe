<?php

namespace ContinuousPipe\Adapter\Kubernetes\ReverseTransformer;

use ContinuousPipe\Model\Component;
use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\Container;
use Kubernetes\Client\Model\ContainerStatus;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\LoadBalancerIngress;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\ServiceSpecification;
use Kubernetes\Client\NamespaceClient;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

class ComponentTransformer
{
    /**
     * @param NamespaceClient       $namespaceClient
     * @param ReplicationController $replicationController
     *
     * @throws \InvalidArgumentException
     *
     * @return Component
     */
    public function getComponentFromReplicationController(NamespaceClient $namespaceClient, ReplicationController $replicationController)
    {
        $replicationControllerName = $replicationController->getMetadata()->getName();
        $containers = $replicationController->getSpecification()->getPodTemplateSpecification()->getPodSpecification()->getContainers();
        if (0 == count($containers)) {
            throw new \InvalidArgumentException('The pod specification should have at least one container');
        }

        return new Component(
            $replicationControllerName,
            $replicationControllerName,
            new Component\Specification(
                $this->getComponentSource($containers[0]),
                $this->getComponentAccessibility($namespaceClient, $replicationController->getMetadata()->getName()),
                new Component\Scalability(true, $replicationController->getSpecification()->getReplicas())
            ),
            [],
            [],
            $this->getReplicationControllerStatus($namespaceClient, $replicationController),
            new Component\DeploymentStrategy(null, null, false, false)
        );
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param Deployment      $deployment
     *
     * @return Component
     */
    public function getComponentFromDeployment(NamespaceClient $namespaceClient, Deployment $deployment)
    {
        $name = $deployment->getMetadata()->getName();
        $replicas = $deployment->getSpecification()->getReplicas();

        $containers = $deployment->getSpecification()->getTemplate()->getPodSpecification()->getContainers();
        if (0 === count($containers)) {
            throw new \InvalidArgumentException('No container found in deployment\'s specification');
        }

        return new Component(
            $name,
            $name,
            new Component\Specification(
                $this->getComponentSource($containers[0]),
                $this->getComponentAccessibility($namespaceClient, $name),
                new Component\Scalability(true, $replicas)
            ),
            [],
            [],
            $this->getDeploymentStatus($namespaceClient, $deployment),
            new Component\DeploymentStrategy(null, null, false, false)
        );
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
     * @param NamespaceClient $namespaceClient
     * @param string          $serviceName
     *
     * @return Component\Accessibility
     */
    private function getComponentAccessibility(NamespaceClient $namespaceClient, $serviceName)
    {
        try {
            $service = $namespaceClient->getServiceRepository()->findOneByName($serviceName);

            $externalService = $service->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER;

            return new Component\Accessibility(true, $externalService);
        } catch (ServiceNotFound $e) {
            return new Component\Accessibility(false, false);
        }
    }

    /**
     * @param NamespaceClient       $namespaceClient
     * @param ReplicationController $replicationController
     *
     * @return Component\Status
     */
    private function getReplicationControllerStatus(NamespaceClient $namespaceClient, ReplicationController $replicationController)
    {
        $pods = $namespaceClient->getPodRepository()->findByReplicationController($replicationController)->getPods();
        $healthyPods = $this->filterHealthyPods($pods);
        $serviceName = $replicationController->getMetadata()->getName();
        $replicas = $replicationController->getSpecification()->getReplicas();

        if (count($healthyPods) == $replicas) {
            $status = Component\Status::HEALTHY;
        } elseif (count($healthyPods) > 0) {
            $status = Component\Status::WARNING;
        } else {
            $status = Component\Status::UNHEALTHY;
        }

        return new Component\Status($status, $this->getComponentPublicEndpoints($namespaceClient, $serviceName));
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param Deployment      $deployment
     *
     * @return Component\Status
     */
    private function getDeploymentStatus(NamespaceClient $namespaceClient, Deployment $deployment)
    {
        $pods = $namespaceClient->getPodRepository()->findByLabels(
            $deployment->getSpecification()->getSelector()
        )->getPods();

        $healthyPods = $this->filterHealthyPods($pods);
        $serviceName = $deployment->getMetadata()->getName();
        $replicas = $deployment->getSpecification()->getReplicas();

        if (count($healthyPods) == $replicas) {
            $status = Component\Status::HEALTHY;
        } elseif (count($healthyPods) > 0) {
            $status = Component\Status::WARNING;
        } else {
            $status = Component\Status::UNHEALTHY;
        }

        return new Component\Status($status, $this->getComponentPublicEndpoints($namespaceClient, $serviceName));
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param string          $serviceName
     *
     * @return array
     */
    private function getComponentPublicEndpoints(NamespaceClient $namespaceClient, $serviceName)
    {
        try {
            $service = $namespaceClient->getServiceRepository()->findOneByName($serviceName);
        } catch (ServiceNotFound $e) {
            return [];
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $publicEndpoints = [];

        try {
            $ingressesPath = 'status.loadBalancer.ingresses';

            /** @var LoadBalancerIngress[] $ingresses */
            $ingresses = $accessor->getValue($service, $ingressesPath);
            if (!is_array($ingresses)) {
                throw new UnexpectedTypeException($ingresses, new PropertyPath($ingressesPath), 0);
            }

            foreach ($ingresses as $ingress) {
                if ($hostname = $ingress->getHostname()) {
                    $publicEndpoints[] = $hostname;
                }

                if ($ip = $ingress->getIp()) {
                    $publicEndpoints[] = $ip;
                }
            }
        } catch (ExceptionInterface $e) {
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
            if (null === ($status = $pod->getStatus())) {
                return false;
            } elseif (count($status->getContainerStatuses()) === 0) {
                return false;
            }

            return array_reduce($status->getContainerStatuses(), function ($ready, ContainerStatus $containerStatus) {
                return $ready && $containerStatus->isReady();
            }, true);
        });
    }
}
