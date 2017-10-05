<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Pipe\Kubernetes\Tests\PublicEndpoint\PredictableServiceWaiter;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\HookableServiceRepository;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\Trace\TraceableServiceRepository;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\Annotation;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\Label;
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
     * @Given the service :arg1 have the selector :arg2 and type :arg3 with the ports:
     */
    public function iHaveAServiceWithTheSelector($name, $selector, $type = null, TableNode $portsTable = null)
    {
        $this->iHaveAnExistingService($name);
        $service = $this->serviceRepository->findOneByName($name);
        $selector = $this->selectorFromString($selector);
        $ports = $portsTable === null ? [] : array_map(function(array $row) {
            return new ServicePort(
                $row['name'],
                $row['port'],
                $row['protocol'],
                isset($row['targetPort']) ? $row['targetPort'] : null
            );
        }, $portsTable->getHash());

        $service = new Service(
            $service->getMetadata(),
            new ServiceSpecification(
                $selector,
                $ports,
                $type ?: ServiceSpecification::TYPE_CLUSTER_IP
            ),
            $service->getStatus()
        );

        $this->serviceRepository->update($service);
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
                new ObjectMetadata($name, new KeyValueObjectList([
                    new Label('component-identifier', $componentName ?: $name),
                ])),
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

        $ingresses = [];
        if ($status = $service->getStatus()) {
            $ingresses = $service->getStatus()->getLoadBalancer()->getIngresses();
            foreach ($ingresses as $ingress) {
                if ($ingress->getIp() == $address) {
                    return;
                }
            }
        }
        $ingresses[] = new LoadBalancerIngress($address);

        $this->serviceRepository->update(new Service(
            $service->getMetadata(),
            $service->getSpecification(),
            new ServiceStatus(new LoadBalancerStatus($ingresses))
        ));
        $this->serviceRepository->clear();
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
        $this->serviceRepository->clear();
    }

    /**
     * @Given the service :name have the public hostname :hostname
     */
    public function theServiceHaveThePublicHostname($name, $hostname)
    {
        $service = $this->serviceRepository->findOneByName($name);

        $ingresses = [];
        if ($status = $service->getStatus()) {
            $ingresses = $status->getLoadBalancer()->getIngresses();
            foreach ($ingresses as $ingress) {
                if ($ingress->getHostname() == $hostname) {
                    return;
                }
            }
        }
        $ingresses[] = new LoadBalancerIngress(null, $hostname);

        $this->serviceRepository->update(new Service(
            $service->getMetadata(),
            $service->getSpecification(),
            new ServiceStatus(new LoadBalancerStatus($ingresses))
        ));
        $this->serviceRepository->clear();
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
     * @Then the annotation :annotationName of the service :serviceName should contain an entry with the JSON key :key
     */
    public function theAnnotationOfTheServiceShouldContainAnEntryWithTheJsonKey($annotationName, $serviceName, $key)
    {
        $service = $this->findServiceByNameInList($this->serviceRepository->getCreated(), $serviceName);
        $annotation = $service->getMetadata()->getAnnotationList()->get($annotationName);
        $rows = \GuzzleHttp\json_decode($annotation->getValue(), true);

        foreach ($rows as $row) {
            if (array_key_exists($key, $row)) {
                return;
            }
        }

        throw new \RuntimeException('The key was not found');
    }

    /**
     * @Then the annotation :annotationName of the service :serviceName should contain the following keys in its JSON:
     */
    public function theAnnotationOfTheServiceShouldContainTheFollowingKeysInItsJson($annotationName, $serviceName, TableNode $table)
    {
        $service = $this->findServiceByNameInList($this->serviceRepository->getCreated(), $serviceName);
        $annotation = $service->getMetadata()->getAnnotationList()->get($annotationName);
        $row = \GuzzleHttp\json_decode($annotation->getValue(), true);

        $this->findKeysInRow($table, $row);
    }

    /**
     * @Then the annotation :annotationName of the service :serviceName should contain an entry the following keys in its JSON:
     */
    public function theAnnotationOfTheServiceShouldContainAnEntryTheFollowingKeysInItsJson($annotationName, $serviceName, TableNode $table)
    {
        $service = $this->findServiceByNameInList($this->serviceRepository->getCreated(), $serviceName);
        $annotation = $service->getMetadata()->getAnnotationList()->get($annotationName);
        $annotationRows = \GuzzleHttp\json_decode($annotation->getValue(), true);

        foreach ($annotationRows as $row) {
            try {
                $this->findKeysInRow($table, $row);

                return;
            } catch (\Exception $e) {
                // Try the next one...
            }
        }

        throw new \RuntimeException('Such entry not found');
    }

    /**
     * @Then the service :name should have the type :type
     */
    public function theServiceShouldHaveTheType($name, $type)
    {
        $service = $this->serviceRepository->findOneByName($name);

        if ($service->getSpecification()->getType() != $type) {
            throw new \RuntimeException(sprintf(
                'Expected type "%s" but got "%s"',
                $type,
                $service->getSpecification()->getType()
            ));
        }
    }


    /**
     * @Then the service :name should have the following annotations:
     */
    public function theServiceShouldHaveTheFollowingAnnotations($name, TableNode $table)
    {
        $service = $this->findServiceByNameInList($this->serviceRepository->getCreated(), $name);
        $foundAnnotations = $service->getMetadata()->getAnnotationList();

        foreach ($table->getHash() as $row) {
            if ($annotation = $foundAnnotations->get($row['name'])) {
                if ($annotation->getValue() == $row['value']) {
                    return;
                }
            }
        }

        throw new \RuntimeException('Annotation not found');
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

    /**
     * @param TableNode $table
     * @param array $row
     */
    private function findKeysInRow(TableNode $table, array $row)
    {
        foreach ($table->getHash() as $tableRow) {
            if (!array_key_exists($tableRow['name'], $row)) {
                throw new \RuntimeException(sprintf(
                    'Expected to find the key "%s" but not found',
                    $tableRow['name']
                ));
            }

            $foundValue = $row[$tableRow['name']];
            if ($foundValue != $tableRow['value']) {
                throw new \RuntimeException(sprintf(
                    'Found value %s for key "%s"',
                    $foundValue,
                    $tableRow['name']
                ));
            }
        }
    }
}
