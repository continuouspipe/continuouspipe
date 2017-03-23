<?php

namespace ContinuousPipe\Adapter\Kubernetes\ReverseTransformer;

use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\LoadBalancerIngress;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

class DefaultComponentPublicEndpointResolver implements ComponentPublicEndpointResolver
{
    const INGRESSES_PATH = 'status.loadBalancer.ingresses';
    /**
     * @var PropertyAccessor
     */
    private $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(KubernetesObject $serviceOrIngress)
    {
        try {
            /** @var LoadBalancerIngress[] $ingresses */
            $ingresses = $this->accessor->getValue($serviceOrIngress, self::INGRESSES_PATH);
            if (!is_array($ingresses)) {
                throw new UnexpectedTypeException($ingresses, new PropertyPath(self::INGRESSES_PATH), 0);
            }

            foreach ($ingresses as $ingress) {
                if ($hostname = $ingress->getHostname()) {
                    return $hostname;
                }

                if ($ip = $ingress->getIp()) {
                    return $ip;
                }
            }
        } catch (ExceptionInterface $e) {
        }
    }
}
