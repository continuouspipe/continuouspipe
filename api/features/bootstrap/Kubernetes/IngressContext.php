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
        $this->hookableIngressRepository->addFindOneByNameHooks(function(Ingress $ingress) use ($name, $address) {

            if ($ingress->getMetadata()->getName() == $name) {
                $ingress = new Ingress(
                    $ingress->getMetadata(),
                    $ingress->getSpecification(),
                    new IngressStatus(new LoadBalancerStatus([
                        new LoadBalancerIngress($address)
                    ]))
                );
            }

            return $ingress;
        });
    }
}
