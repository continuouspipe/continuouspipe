<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Tests\PublicEndpoint\PredictableServiceWaiter;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableServiceRepository;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\LoadBalancerIngress;
use Kubernetes\Client\Model\LoadBalancerStatus;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;
use Kubernetes\Client\Model\ServiceStatus;
use Kubernetes\Client\Repository\ServiceRepository;

class ServiceContext implements Context
{
    /**
     * @var PredictableServiceWaiter
     */
    private $serviceWaiter;

    /**
     * @var TraceableServiceRepository
     */
    private $serviceRepository;

    /**
     * @param PredictableServiceWaiter $serviceWaiter
     * @param TraceableServiceRepository $serviceRepository
     */
    public function __construct(PredictableServiceWaiter $serviceWaiter, TraceableServiceRepository $serviceRepository)
    {
        $this->serviceWaiter = $serviceWaiter;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * @Given the service :name will be created with the public endpoint :address
     */
    public function theServiceWillBeCreatedWithThePublicEndpoint($name, $address)
    {
        $this->serviceWaiter->add(new PublicEndpoint($name, $address));
    }

    /**
     * @Given I have a service :name with the selector :selector
     */
    public function iHaveAServiceWithTheSelector($name, $selector)
    {
        $selector = $this->selectorFromString($selector);

        $this->serviceRepository->create(new Service(
            new ObjectMetadata($name),
            new ServiceSpecification($selector)
        ));

        $this->serviceRepository->clear();
    }

    /**
     * @Given I have an existing service :name
     */
    public function iHaveAnExistingService($name)
    {
        try {
            $this->serviceRepository->findOneByName($name);
        } catch (ServiceNotFound $e) {
            $this->serviceRepository->create(new Service(new ObjectMetadata($name)));
        }
    }

    /**
     * @Then the service :name should not be updated
     */
    public function theServiceShouldNotBeUpdated($name)
    {
        try {
            $this->findServiceByNameInList($this->serviceRepository->getUpdated(), $name);

            throw new \RuntimeException('Service found in list of updated');
        } catch (ServiceNotFound $e) {
        }
    }

    /**
     * @Then the service :name should not be deleted
     */
    public function theServiceShouldNotBeDeleted($name)
    {
        try {
            $this->findServiceByNameInList($this->serviceRepository->getDeleted(), $name);

            throw new \RuntimeException('Service found in list of delete');
        } catch (ServiceNotFound $e) {
        }
    }

    /**
     * @Then the service :name should be deleted
     */
    public function theServiceShouldBeDeleted($name)
    {
        $this->findServiceByNameInList($this->serviceRepository->getDeleted(), $name);
    }

    /**
     * @Then the service :name should be created
     */
    public function theServiceShouldBeCreated($name)
    {
        $this->findServiceByNameInList($this->serviceRepository->getCreated(), $name);
    }

    /**
     * @Then the service :name should not be created
     */
    public function theServiceShouldNotBeCreated($name)
    {
        try {
            $this->findServiceByNameInList($this->serviceRepository->getCreated(), $name);

            throw new \RuntimeException('Service found in list of created services');
        } catch (ServiceNotFound $e) {
        }
    }


    /**
     * @Given the service :name have the public IP :address
     */
    public function theServiceHaveThePublicEndpoint($name, $address)
    {
        $service = $this->serviceRepository->findOneByName($name);

        $this->serviceRepository->update(new Service(
            $service->getMetadata(),
            $service->getSpecification(),
            new ServiceStatus(new LoadBalancerStatus([
                new LoadBalancerIngress($address)
            ]))
        ));
    }

    /**
     * @Given the service :name have the public hostname :hostname
     */
    public function theServiceHaveThePublicHostname($name, $hostname)
    {
        $service = $this->serviceRepository->findOneByName($name);

        $this->serviceRepository->update(new Service(
            $service->getMetadata(),
            $service->getSpecification(),
            new ServiceStatus(new LoadBalancerStatus([
                new LoadBalancerIngress(null, $hostname)
            ]))
        ));
    }

    /**
     * @param Service[] $services
     * @param string $name
     * @return Service
     * @throws ServiceNotFound
     */
    private function findServiceByNameInList(array $services, $name)
    {
        $matchingServices = array_filter($services, function(Service $service) use ($name) {
            return $service->getMetadata()->getName() == $name;
        });

        if (count($matchingServices) == 0) {
            throw new ServiceNotFound(sprintf(
                'No service "%s" found in list',
                $name
            ));
        }

        return current($matchingServices);
    }

    /**
     * @param string $selectorString
     *
     * @return array
     */
    private function selectorFromString($selectorString)
    {
        $selector = [];

        foreach (explode(',', $selectorString) as $string) {
            list($name, $value) = explode('=', $string);

            $selector[$name] = $value;
        }

        return $selector;
    }
}
