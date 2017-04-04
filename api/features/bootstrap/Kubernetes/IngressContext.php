<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookableIngressRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableIngressRepository;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\IngressStatus;
use Kubernetes\Client\Model\LoadBalancerIngress;
use Kubernetes\Client\Model\LoadBalancerStatus;
use Kubernetes\Client\Repository\IngressRepository;

class IngressContext implements Context
{
    /**
     * @var TraceableIngressRepository
     */
    private $traceableIngressRepository;

    /**
     * @var IngressRepository
     */
    private $ingressRepository;

    /**
     * @var HookableIngressRepository
     */
    private $hookableIngressRepository;

    /**
     * @param TraceableIngressRepository $traceableIngressRepository
     * @param HookableIngressRepository $hookableIngressRepository
     * @param IngressRepository $ingressRepository
     */
    public function __construct(TraceableIngressRepository $traceableIngressRepository, HookableIngressRepository $hookableIngressRepository, IngressRepository $ingressRepository)
    {
        $this->traceableIngressRepository = $traceableIngressRepository;
        $this->hookableIngressRepository = $hookableIngressRepository;
        $this->ingressRepository = $ingressRepository;
    }

    /**
     * @Then the ingress named :name should be created
     */
    public function theIngressNamedShouldBeCreated($name)
    {
        $created = $this->traceableIngressRepository->getCreated();
        $matchingIngresses = array_filter($created, function(Ingress $ingress) use ($name) {
            return $ingress->getMetadata()->getName() == $name;
        });

        if (count($matchingIngresses) == 0) {
            throw new \RuntimeException('No ingress found');
        }
    }

    /**
     * @Then the ingress named :name should not be created
     */
    public function theIngressNamedShouldNotBeCreated($name)
    {
        $created = $this->traceableIngressRepository->getCreated();
        $matchingIngresses = array_filter($created, function(Ingress $ingress) use ($name) {
            return $ingress->getMetadata()->getName() == $name;
        });

        if (count($matchingIngresses) != 0) {
            throw new \RuntimeException('Ingress found');
        }
    }

    /**
     * @Then the ingress named :name should have the hostname :hostname
     */
    public function theIngressNamedShouldHaveTheHostname($name, $hostname)
    {
        $ingress = $this->ingressRepository->findOneByName($name);

        foreach ($ingress->getSpecification()->getRules() as $rule) {
            if ($rule->getHost() == $hostname) {
                return;
            }
        }

        throw new \RuntimeException('No rule matching the hostname found');
    }

    /**
     * @Then the ingress named :name should have the class :class
     */
    public function theIngressNamedShouldHaveTheClass($name, $class)
    {
        $ingress = $this->ingressRepository->findOneByName($name);
        $annotation = $ingress->getMetadata()->getAnnotationList()->get('kubernetes.io/ingress.class');

        if (null === $annotation) {
            throw new \RuntimeException('Class annotation not found');
        }

        if ($annotation->getValue() != $class) {
            throw new \RuntimeException(sprintf(
                'Found class "%s" instead',
                $annotation->getValue()
            ));
        }
    }

    /**
     * @Then the ingress named :name should have the backend service :service on port :port
     */
    public function theIngressNamedShouldHaveTheBackendServiceOnPort($name, $service, $port)
    {
        $ingress = $this->ingressRepository->findOneByName($name);

        foreach ($ingress->getSpecification()->getRules() as $rule) {
            foreach ($rule->getHttp()->getPaths() as $path) {
                if ($path->getBackend()->getServiceName() == $service && $path->getBackend()->getServicePort() == $port) {
                    return;
                }
            }
        }

        throw new \RuntimeException('The backend was not found in the rules\' paths');
    }

    /**
     * @Then the ingress named :name should not be using secure backends
     */
    public function theIngressNamedShouldNotBeUsingSecureBackends($name)
    {
        $ingress = $this->ingressRepository->findOneByName($name);
        $annotation = $ingress->getMetadata()->getAnnotationList()->get('ingress.kubernetes.io/secure-backends');

        if (null !== $annotation && $annotation->getValue() == 'true') {
            throw new \RuntimeException('It is apparently using the secure backends!');
        }
    }

    /**
     * @Then the ingress named :name should be using secure backends
     */
    public function theIngressNamedShouldBeUsingSecureBackends($name)
    {
        $ingress = $this->ingressRepository->findOneByName($name);
        $annotation = $ingress->getMetadata()->getAnnotationList()->get('ingress.kubernetes.io/secure-backends');

        if (null === $annotation || $annotation->getValue() != 'true') {
            throw new \RuntimeException('It is apparently NOT using the secure backends!');
        }
    }

    /**
     * @Then the ingress named :name should have a SSL certificate for the host :host
     */
    public function theIngressNamedShouldHaveASslCertificateForTheHost($name, $host)
    {
        $ingress = $this->ingressRepository->findOneByName($name);

        foreach ($ingress->getSpecification()->getTls() as $tls) {
            if (null === $tls->getHosts()) {
                continue;
            }

            foreach ($tls->getHosts() as $tlsHost) {
                if ($tlsHost == $host) {
                    return $tls;
                }
            }
        }

        throw new \RuntimeException('No TLS certificate found for this host');
    }

    /**
     * @Then the ingress named :name should have :count SSL certificate
     */
    public function theIngressNamedShouldHaveSslCertificate($name, $count)
    {
        $ingress = $this->ingressRepository->findOneByName($name);
        $numberOfCertificates = count($ingress->getSpecification()->getTls());

        if ($count != $numberOfCertificates) {
            throw new \RuntimeException(sprintf(
                'Expected %d certificates but found %d',
                $count,
                $numberOfCertificates
            ));
        }
    }

    /**
     * @Given the ingress :name will be created with the public DNS address :address
     */
    public function theIngressWillBeCreatedWithThePublicDnsAddress($name, $address)
    {
        $this->theIngressWillBeCreatedWithTheStatus($name, new LoadBalancerIngress(null, $address));
    }

    /**
     * @Given the ingress :name will be created with the public IP :ip
     */
    public function theIngressWillBeCreatedWithThePublicIp($name, $ip)
    {
        $this->theIngressWillBeCreatedWithTheStatus($name, new LoadBalancerIngress($ip));
    }

    /**
     * @param $name
     * @param $status
     */
    private function theIngressWillBeCreatedWithTheStatus($name, $status)
    {
        $this->hookableIngressRepository->addFindOneByNameHooks(function (Ingress $ingress) use ($name, $status) {
            if ($ingress->getMetadata()->getName() == $name) {
                $ingress = new Ingress(
                    $ingress->getMetadata(),
                    $ingress->getSpecification(),
                    new IngressStatus(new LoadBalancerStatus([
                        $status
                    ]))
                );
            }

            return $ingress;
        });
    }

    /**
     * @Then the ingress named :name should not have a backend service
     */
    public function theIngressNamedShouldNotHaveABackendService($name)
    {
        $ingress = $this->ingressRepository->findOneByName($name);

        if ($ingress->getSpecification()->getBackend() !== null) {
            throw new \RuntimeException('A backend was found for the ingress');
        }
    }
}
