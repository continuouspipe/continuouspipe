<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\Ingress\Factory;

use ContinuousPipe\Adapter\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointFactory;
use ContinuousPipe\Adapter\Kubernetes\Transformer\TransformationException;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Component\Endpoint;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\IngressBackend;
use Kubernetes\Client\Model\IngressSpecification;
use Kubernetes\Client\Model\IngressTls;
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
            $type = count($endpoint->getSslCertificates()) > 0 ? ServiceSpecification::TYPE_CLUSTER_IP : ServiceSpecification::TYPE_LOAD_BALANCER;
        }

        $service = $this->createService($component, $endpoint, $type);

        if ($type !== ServiceSpecification::TYPE_LOAD_BALANCER) {
            $service->getMetadata()->getLabelList()->add(
                new Label('source-of-ingress', $endpoint->getName())
            );

            $sslCertificatesSecrets = array_map(function (Endpoint\SslCertificate $sslCertificate) use ($endpoint) {
                return $this->createSslCertificateSecret($endpoint, $sslCertificate);
            }, $endpoint->getSslCertificates());

            $ingress = $this->createIngress($component, $service, $sslCertificatesSecrets);

            return array_merge($sslCertificatesSecrets, [$service, $ingress]);
        }

        return [$service];
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
        $objectMetadata = new ObjectMetadata($endpoint->getName(), $labels);
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
     * @param Component                 $component
     * @param Service                   $service
     * @param Endpoint\SslCertificate[] $sslCertificatesSecrets
     *
     * @return Ingress
     */
    private function createIngress(Component $component, Service $service, array $sslCertificatesSecrets)
    {
        $labels = $this->namingStrategy->getLabelsByComponent($component);
        $labels->add(new Label('service-type', $service->getSpecification()->getType()));

        return new Ingress(
            new ObjectMetadata(
                $service->getMetadata()->getName(),
                $labels
            ),
            new IngressSpecification(
                new IngressBackend(
                    $service->getMetadata()->getName(),
                    $service->getSpecification()->getPorts()[0]->getPort()
                ),
                array_map(function (Secret $secret) {
                    return new IngressTls(
                        $secret->getMetadata()->getName()
                    );
                }, $sslCertificatesSecrets)
            )
        );
    }
}
