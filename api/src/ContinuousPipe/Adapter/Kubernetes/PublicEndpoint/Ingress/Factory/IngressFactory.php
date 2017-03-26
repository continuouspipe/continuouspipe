<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\Ingress\Factory;

use ContinuousPipe\Adapter\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointFactory;
use ContinuousPipe\Adapter\Kubernetes\Transformer\TransformationException;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Component\Endpoint;
use Kubernetes\Client\Model\Annotation;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\IngressBackend;
use Kubernetes\Client\Model\IngressHttpRule;
use Kubernetes\Client\Model\IngressHttpRulePath;
use Kubernetes\Client\Model\IngressRule;
use Kubernetes\Client\Model\IngressSpecification;
use Kubernetes\Client\Model\IngressTls;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\Label;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServicePort;
use Kubernetes\Client\Model\ServiceSpecification;

class IngressFactory implements EndpointFactory
{
    /**
     * @var NamingStrategy
     */
    private $namingStrategy;

    /**
     * @param NamingStrategy $namingStrategy
     */
    public function __construct(NamingStrategy $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectsFromEndpoint(Component $component, Endpoint $endpoint)
    {
        if (null === ($type = $endpoint->getType())) {
            if (null !== $endpoint->getIngress() || count($endpoint->getSslCertificates()) > 0) {
                $type = 'ingress';
            } else {
                $type = 'service';
            }
        }

        if ($type == 'ingress') {
            if (null === $endpoint->getIngress() && count($endpoint->getSslCertificates()) > 0) {
                $serviceType = ServiceSpecification::TYPE_NODE_PORT;
            } else {
                $serviceType = ServiceSpecification::TYPE_CLUSTER_IP;
            }
        } else {
            $serviceType = ServiceSpecification::TYPE_LOAD_BALANCER;
        }

        $service = $this->createService($component, $endpoint, $serviceType);

        // The `LoadBalancer` service is enough.
        if ($serviceType === ServiceSpecification::TYPE_LOAD_BALANCER) {
            return [
                $service,
            ];
        }

        if (null === ($endpointIngress = $endpoint->getIngress())) {
            $endpointIngress = new Endpoint\EndpointIngress(null, []);
        }

        $service->getMetadata()->getLabelList()->add(
            new Label('source-of-ingress', $endpoint->getName())
        );

        $sslCertificatesSecrets = array_map(function (Endpoint\SslCertificate $sslCertificate) use ($endpoint) {
            return $this->createSslCertificateSecret($endpoint, $sslCertificate);
        }, $endpoint->getSslCertificates());

        $ingress = $this->createIngress(
            $component,
            $service,
            $endpointIngress->getClass(),
            array_map(function (Secret $secret) {
                return new IngressTls(
                    $secret->getMetadata()->getName()
                );
            }, $sslCertificatesSecrets),
            $endpointIngress->getRules()
        );

        return array_merge($sslCertificatesSecrets, [$service, $ingress]);
    }

    /**
     * @param Component $component
     * @param Endpoint  $endpoint
     * @param string    $type
     *
     * @return Service
     *
     * @throws TransformationException
     */
    private function createService(Component $component, Endpoint $endpoint, string $type)
    {
        $ports = array_map(function (Component\Port $port) {
            return new ServicePort($port->getIdentifier(), $port->getPort(), $port->getProtocol());
        }, $component->getSpecification()->getPorts());

        if (count($ports) == 0) {
            throw new TransformationException('The component should expose at least one port');
        }

        $labels = $this->namingStrategy->getLabelsByComponent($component);
        $serviceSpecification = new ServiceSpecification($labels->toAssociativeArray(), $ports, $type);
        $objectMetadata = new ObjectMetadata($endpoint->getName(), $labels, KeyValueObjectList::fromAssociativeArray($endpoint->getAnnotations(), Annotation::class));
        $service = new Service($objectMetadata, $serviceSpecification);

        return $service;
    }

    /**
     * @param Endpoint                $endpoint
     * @param Endpoint\SslCertificate $sslCertificate
     *
     * @return Secret
     */
    private function createSslCertificateSecret(Endpoint $endpoint, Endpoint\SslCertificate $sslCertificate)
    {
        return new Secret(
            new ObjectMetadata(
                implode('-', [$endpoint->getName(), $sslCertificate->getName()])
            ),
            [
                'tls.crt' => $sslCertificate->getCert(),
                'tls.key' => $sslCertificate->getKey(),
            ],
            'Opaque'
        );
    }

    /**
     * @param Component $component
     * @param Service $service
     * @param string $class
     * @param array $tlsCertificates
     * @param array $rules
     *
     * @return Ingress
     */
    private function createIngress(Component $component, Service $service, string $class = null, array $tlsCertificates = [], array $rules = [])
    {
        $labels = $this->namingStrategy->getLabelsByComponent($component);
        $labels->add(new Label('service-type', $service->getSpecification()->getType()));

        $annotations = new KeyValueObjectList();

        if (null !== $class) {
            $annotations->add(new Annotation('kubernetes.io/ingress.class', $class));
        }

        $portNumbers = array_map(function(ServicePort $port) {
            return (int) $port->getPort();
        }, $service->getSpecification()->getPorts());

        $exposedPort = current($portNumbers);
        if (in_array(443, $portNumbers)) {
            $exposedPort = 443;
            $annotations->add(new Annotation('ingress.kubernetes.io/secure-backends', 'true'));
        } elseif (in_array(80, $portNumbers)) {
            $exposedPort = 80;
        }

        $ingressBackend = new IngressBackend(
            $service->getMetadata()->getName(),
            $exposedPort
        );

        return new Ingress(
            new ObjectMetadata(
                $service->getMetadata()->getName(),
                $labels,
                $annotations
            ),
            new IngressSpecification(
                $ingressBackend,
                $tlsCertificates,
                array_map(function(IngressRule $rule) use ($ingressBackend) {
                    if (null === ($http = $rule->getHttp())) {
                        $http = new IngressHttpRule([
                            new IngressHttpRulePath($ingressBackend),
                        ]);
                    }

                    return new IngressRule(
                        $rule->getHost(),
                        $http
                    );
                }, $rules)
            )
        );
    }
}
