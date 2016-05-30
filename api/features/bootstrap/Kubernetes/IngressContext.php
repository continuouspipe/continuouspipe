<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableIngressRepository;
use Kubernetes\Client\Model\Ingress;
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
     * @param TraceableIngressRepository $traceableIngressRepository
     * @param IngressRepository $ingressRepository
     */
    public function __construct(TraceableIngressRepository $traceableIngressRepository, IngressRepository $ingressRepository)
    {
        $this->traceableIngressRepository = $traceableIngressRepository;
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
}
