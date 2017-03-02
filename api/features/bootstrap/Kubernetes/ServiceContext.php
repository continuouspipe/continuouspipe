<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Adapter\Kubernetes\Tests\PublicEndpoint\PredictableServiceWaiter;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookableServiceRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableServiceRepository;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\Annotation;
use Kubernetes\Client\Model\LoadBalancerIngress;
use Kubernetes\Client\Model\LoadBalancerStatus;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServicePort;
use Kubernetes\Client\Model\ServiceSpecification;
use Kubernetes\Client\Model\ServiceStatus;
use Kubernetes\Client\Repository\ServiceRepository;

class ServiceContext implements Context
{
    /**
     * @var TraceableServiceRepository
     */
    private $serviceRepository;

    /**
     * @var HookableServiceRepository
     */
    private $hookableServiceRepository;

    /**
     * @param HookableServiceRepository $hookableServiceRepository
     * @param TraceableServiceRepository $serviceRepository
     */
    public function __construct(HookableServiceRepository $hookableServiceRepository, TraceableServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
        $this->hookableServiceRepository = $hookableServiceRepository;
    }

    /**
     * @Given the service :name will be created with the public IP :address
     */
    public function theServiceWillBeCreatedWithThePublicEndpoint($name, $address)
    {
        $this->hookableServiceRepository->addFindOneByNameHooks(function(Service $service) use ($name, $address) {
            if ($service->getMetadata()->getName() == $name && $service->getStatus() === null) {
                $service = new Service(
                    $service->getMetadata(),
                    $service->getSpecification(),
                    new ServiceStatus(new LoadBalancerStatus([
                        new LoadBalancerIngress($address)
                    ]))
                );
            }

            return $service;
        });
    }

    /**
     * @Given the service :name will be created with the public DNS address :address
     */
    public function theServiceWillBeCreatedWithThePublicDnsAddress($name, $address)
    {
        $this->hookableServiceRepository->addFindOneByNameHooks(function(Service $service) use ($name, $address) {
            if ($service->getMetadata()->getName() == $name) {
                $service = new Service(
                    $service->getMetadata(),
                    $service->getSpecification(),
                    new ServiceStatus(new LoadBalancerStatus([
                        new LoadBalancerIngress(null, $address)
                    ]))
                );
            }

            return $service;
        });
    }

    /**
     * @Given I have a service :name with the selector :selector
     * @Given I have a service :name with the selector :selector and type :type
     * @Given I have a service :name with the selector :selector and type :type with the ports:
     */
    public function iHaveAServiceWithTheSelector($name, $selector, $type = null, TableNode $portsTable = null)
    {
        $selector = $this->selectorFromString($selector);
        $ports = $portsTable === null ? [] : array_map(function(array $row) {
            return new ServicePort(
                $row['name'],
                $row['port'],
                $row['protocol'],
                isset($row['targetPort']) ? $row['targetPort'] : null
            );
        }, $portsTable->getHash());

        $this->serviceRepository->create(new Service(
            new ObjectMetadata($name),
            new ServiceSpecification($selector, $ports, $type ?: ServiceSpecification::TYPE_CLUSTER_IP)
        ));

        $this->serviceRepository->clear();
    }

    /**
     * @Given I have an existing service :name
     * @Given there is a service :name for the component :componentName
     */
    public function iHaveAnExistingService($name, $componentName = null)
    {
        try {
            $this->serviceRepository->findOneByName($name);
        } catch (ServiceNotFound $e) {
            $this->serviceRepository->create(new Service(
                new ObjectMetadata($name),
                new ServiceSpecification(
                    ['component-identifier' => $componentName ?: $name]
                )
            ));
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
     * @Then the service :name should be updated
     */
    public function theServiceShouldBeUpdated($name)
    {
        $this->findServiceByNameInList($this->serviceRepository->getUpdated(), $name);
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
     * @Given the service :name have the following annotations:
     */
    public function theServiceHaveTheFollowingAnnotations($name, TableNode $table)
    {
        $service = $this->serviceRepository->findOneByName($name);
        $metadata = $service->getMetadata();

        foreach ($table->getHash() as $row) {
            $metadata->getAnnotationList()->add(new Annotation(
                $row['name'],
                $row['value']
            ));
        }

        $this->serviceRepository->update(new Service(
            $metadata,
            $service->getSpecification(),
            $service->getStatus()
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
     * @Then the service :name should contain the following annotations:
     */
    public function theServiceShouldContainTheFollowingAnnotations($name, TableNode $table)
    {
        $service = $this->findServiceByNameInList($this->serviceRepository->getCreated(), $name);
        $annotations = $service->getMetadata()->getAnnotationsAsAssociativeArray();

        foreach ($table->getHash() as $row) {
            if (!array_key_exists($row['name'], $annotations)) {
                throw new \RuntimeException(sprintf(
                    'Expected to find annotation "%s" but not found',
                    $row['name']
                ));
            }

            $foundValue = $annotations[$row['name']];
            if ($foundValue != $row['value']) {
                throw new \RuntimeException(sprintf(
                    'Found value %s for annotation "%s"',
                    $foundValue,
                    $row['name']
                ));
            }
        }
    }
    /**
     * @Then the service :name should have the type :type
     */
    public function theServiceShouldHaveTheType($name, $type)
    {
        $service = $this->findServiceByNameInList($this->serviceRepository->getCreated(), $name);

        if ($service->getSpecification()->getType() != $type) {
            throw new \RuntimeException(sprintf(
                'Expected type "%s" but got "%s"',
                $type,
                $service->getSpecification()->getType()
            ));
        }
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
