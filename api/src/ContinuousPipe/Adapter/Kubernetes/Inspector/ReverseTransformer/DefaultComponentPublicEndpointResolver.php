<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector\ReverseTransformer;

use ContinuousPipe\Adapter\Kubernetes\Inspector\ReverseTransformer\ComponentPublicEndpointResolver;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\LoadBalancerIngress;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

class DefaultComponentPublicEndpointResolver implements ComponentPublicEndpointResolver
{
    const INGRESSES_PATH = 'status.loadBalancer.ingresses';
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PropertyAccessor
     */
    private $accessor;

    public function __construct(LoggerInterface $logger, PropertyAccessor $propertyAccessor = null)
    {
        $this->logger = $logger;
        $this->accessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(KubernetesObject $serviceOrIngress) : array
    {
        $publicEndpoints = [];
        try {
            /** @var LoadBalancerIngress[] $ingresses */
            $ingresses = $this->accessor->getValue($serviceOrIngress, self::INGRESSES_PATH);
            if (!is_array($ingresses)) {
                throw new UnexpectedTypeException($ingresses, new PropertyPath(self::INGRESSES_PATH), 0);
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
            // The endpoint was not found. It's a normal behaviour, the only reason of using
            // exceptions here it's because we are using Symfony's property accessor.
        }

        return $publicEndpoints;
    }
}
