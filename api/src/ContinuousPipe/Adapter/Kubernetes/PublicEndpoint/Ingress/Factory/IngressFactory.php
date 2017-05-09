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
        $ingressType = $this->getIngressType($endpoint);
        $serviceType = $this->getServiceType($endpoint, $ingressType);

        $service = $this->createService($component, $endpoint, $serviceType);

        // The `LoadBalancer` service is enough.
        if ($serviceType === ServiceSpecification::TYPE_LOAD_BALANCER) {
            return [$service,];
        }
        if ($ingressType == 'internal') {
            $this->addLabelToService($service, 'internal-endpoint', 'true');

            return [$service];
        }

        return $this->getIngress($component, $endpoint, $service);
    }

    /**
     * @param Component $component
     * @param Endpoint $endpoint
     * @param string $type
     *
     * @return Service
     *
     * @throws TransformationException
     */
    private function createService(Component $component, Endpoint $endpoint, string $type)
    {
        $ports = array_map(
            function (Component\Port $port) {
                return new ServicePort($port->getIdentifier(), $port->getPort(), $port->getProtocol());
            },
            $component->getSpecification()->getPorts()
        );

        if (count($ports) == 0) {
            throw new TransformationException('The component should expose at least one port');
        }

        $labels = $this->namingStrategy->getLabelsByComponent($component);
        $serviceSpecification = new ServiceSpecification($labels->toAssociativeArray(), $ports, $type);
        $objectMetadata = new ObjectMetadata(
            $endpoint->getName(),
            $labels,
            KeyValueObjectList::fromAssociativeArray($endpoint->getAnnotations(), Annotation::class)
        );

        return new Service($objectMetadata, $serviceSpecification);
    }

    /**
     * @param Endpoint $endpoint
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
            'kubernetes.io/tls'
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
    private function createIngress(
        Component $component,
        Service $service,
        string $class = null,
        array $tlsCertificates = [],
        array $rules = []
    ) {
        $labels = $this->namingStrategy->getLabelsByComponent($component);
        $labels->add(new Label('service-type', $service->getSpecification()->getType()));

        $annotations = new KeyValueObjectList();

        if (null !== $class) {
            $annotations->add(new Annotation('kubernetes.io/ingress.class', $class));
        }

        $ingressBackend = $this->getIngressBackend($service, $annotations);

        return new Ingress(
            new ObjectMetadata(
                $service->getMetadata()->getName(),
                $labels,
                $annotations
            ),
            new IngressSpecification(
                count($rules) > 0 ? null : $ingressBackend,
                $tlsCertificates,
                array_map(
                    function (IngressRule $rule) use ($ingressBackend) {
                        if (null === ($http = $rule->getHttp())) {
                            $http = new IngressHttpRule(
                                [
                                    new IngressHttpRulePath($ingressBackend),
                                ]
                            );
                        }

                        return new IngressRule(
                            $rule->getHost(),
                            $http
                        );
                    },
                    $rules
                )
            )
        );
    }

    /**
     * @param IngressRule[] $rules
     *
     * @return string[]
     */
    private function getHostsFromRules(array $rules) : array
    {
        return array_map(
            function (IngressRule $rule) {
                return $rule->getHost();
            },
            $rules
        );
    }

    private function getIngressType(Endpoint $endpoint): string
    {
        if (null !== ($type = $endpoint->getType())) {
            return $type;
        }

        if (null !== $endpoint->getIngress() || count($endpoint->getSslCertificates()) > 0) {
            return 'ingress';
        }

        return 'service';
    }

    private function getServiceType(Endpoint $endpoint, string $ingressType): string
    {
        if ($ingressType == 'NodePort') {
            return ServiceSpecification::TYPE_NODE_PORT;
        }

        if ($ingressType == 'internal') {
            return ServiceSpecification::TYPE_CLUSTER_IP;
        }

        if ($ingressType != 'ingress') {
            return ServiceSpecification::TYPE_LOAD_BALANCER;
        }

        if (null === ($endpointIngress = $endpoint->getIngress())) {
            if (count($endpoint->getSslCertificates()) > 0) {
                return ServiceSpecification::TYPE_CLUSTER_IP;
            } else {
                return ServiceSpecification::TYPE_NODE_PORT;
            }
        } elseif ($this->classHasToBeNodePort($endpointIngress->getClass())) {
            return ServiceSpecification::TYPE_NODE_PORT;
        }

        return ServiceSpecification::TYPE_CLUSTER_IP;
    }

    private function classHasToBeNodePort(string $class) : bool
    {
        return in_array($class, ['gce']);
    }

    /**
     * @param Endpoint $endpoint
     * @return array
     */
    private function getSslCertificatesSecrets(Endpoint $endpoint)
    {
        return array_map(
            function (Endpoint\SslCertificate $sslCertificate) use ($endpoint) {
                return $this->createSslCertificateSecret($endpoint, $sslCertificate);
            },
            $endpoint->getSslCertificates()
        );
    }

    /**
     * @param $endpointIngress
     * @param $sslCertificatesSecrets
     * @return array
     */
    private function getTlsCertificates($endpointIngress, $sslCertificatesSecrets)
    {
        return array_map(
            function (Secret $secret) use ($endpointIngress) {
                return new IngressTls(
                    $secret->getMetadata()->getName(),
                    $this->getHostsFromRules($endpointIngress->getRules())
                );
            },
            $sslCertificatesSecrets
        );
    }

    /**
     * @param $service
     * @param $labelName
     * @param $labelValue
     */
    private function addLabelToService($service, $labelName, $labelValue)
    {
        $service->getMetadata()->getLabelList()->add(
            new Label($labelName, $labelValue)
        );
    }

    /**
     * @param Component $component
     * @param Endpoint $endpoint
     * @param $service
     * @return array
     */
    private function getIngress(Component $component, Endpoint $endpoint, $service)
    {
        if (null === ($endpointIngress = $endpoint->getIngress())) {
            $endpointIngress = new Endpoint\EndpointIngress(null, []);
        }

        $this->addLabelToService($service, 'source-of-ingress', $endpoint->getName());

        $sslCertificatesSecrets = $this->getSslCertificatesSecrets($endpoint);

        $ingress = $this->createIngress(
            $component,
            $service,
            $endpointIngress->getClass(),
            $this->getTlsCertificates($endpointIngress, $sslCertificatesSecrets),
            $endpointIngress->getRules()
        );

        return array_merge($sslCertificatesSecrets, [$service, $ingress]);
    }

    /**
     * @param Service $service
     * @return array
     */
    private function getPortNumbers(Service $service)
    {
        return array_map(
            function (ServicePort $port) {
                return (int) $port->getPort();
            },
            $service->getSpecification()->getPorts()
        );
    }

    /**
     * @param $portNumbers
     * @param $annotations
     * @return int|mixed
     */
    private function getExposedPort($portNumbers, $annotations)
    {
        $exposedPort = current($portNumbers);
        if (in_array(443, $portNumbers)) {
            $annotations->add(new Annotation('ingress.kubernetes.io/secure-backends', 'true'));
            return 443;
        } elseif (in_array(80, $portNumbers)) {
            return 80;
        }
        return $exposedPort;
    }

    /**
     * @param Service $service
     * @param $annotations
     * @return IngressBackend
     */
    private function getIngressBackend(Service $service, $annotations)
    {
        return new IngressBackend(
            $service->getMetadata()->getName(),
            $this->getExposedPort($this->getPortNumbers($service), $annotations)
        );
    }
}
