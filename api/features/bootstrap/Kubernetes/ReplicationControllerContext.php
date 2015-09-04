<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableReplicationControllerRepository;
use Kubernetes\Client\Exception\ReplicationControllerNotFound;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\Label;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodSpecification;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Repository\PodRepository;

class ReplicationControllerContext implements Context
{
    /**
     * @var TraceableReplicationControllerRepository
     */
    private $replicationControllerRepository;

    /**
     * @var PodRepository
     */
    private $podRepository;

    /**
     * @param TraceableReplicationControllerRepository $replicationControllerRepository
     * @param PodRepository $podRepository
     */
    public function __construct(TraceableReplicationControllerRepository $replicationControllerRepository, PodRepository $podRepository)
    {
        $this->replicationControllerRepository = $replicationControllerRepository;
        $this->podRepository = $podRepository;
    }

    /**
     * @Then the replication controller :name should be created
     */
    public function theReplicationControllerShouldBeCreated($name)
    {
        $this->getReplicationControllerByName($name, $this->replicationControllerRepository->getCreatedReplicationControllers());
    }

    /**
     * @Given I have an existing replication controller :name
     */
    public function iHaveAnExistingReplicationController($name)
    {
        try {
            $this->replicationControllerRepository->findOneByName($name);
        } catch (ReplicationControllerNotFound $e) {
            $this->replicationControllerRepository->create(new ReplicationController(new ObjectMetadata($name)));
        }
    }

    /**
     * @Then the replication controller :name shouldn't be updated
     */
    public function theReplicationControllerShouldnTBeUpdated($name)
    {
        $matchingRCs = $this->getReplicationControllersByName($name, $this->replicationControllerRepository->getUpdatedReplicationControllers());

        if (count($matchingRCs) != 0) {
            throw new \RuntimeException(sprintf('Replication controller "%s" should NOT be updated, found in traces', $name));
        }
    }

    /**
     * @Then the replication controller :name should be updated
     */
    public function theReplicationControllerShouldBeUpdated($name)
    {
        $this->getReplicationControllerByName($name, $this->replicationControllerRepository->getUpdatedReplicationControllers());
    }

    /**
     * @Then the replication controller :name should be created with the following environment variables:
     */
    public function theReplicationControllerShouldBeCreatedWithTheFollowingEnvironmentVariables($name, TableNode $environs)
    {
        $replicationController = $this->getReplicationControllerByName($name, $this->replicationControllerRepository->getCreatedReplicationControllers());
        $containers = $replicationController->getSpecification()->getPodTemplateSpecification()->getPodSpecification()->getContainers();
        $expectedVariables = $environs->getHash();

        foreach ($containers as $container) {
            $foundVariables = [];
            foreach ($container->getEnvironmentVariables() as $variable) {
                $foundVariables[$variable->getName()] = $variable->getValue();
            }

            foreach ($expectedVariables as $expectedVariable) {
                $variableName = $expectedVariable['name'];
                if (!array_key_exists($variableName, $foundVariables)) {
                    throw new \RuntimeException(sprintf(
                        'Variable "%s" not found',
                        $expectedVariable['name']
                    ));
                }

                $foundValue = $foundVariables[$variableName];
                if ($foundValue != $expectedVariable['value']) {
                    throw new \RuntimeException(sprintf(
                        'Found value "%s" in environment variable "%s" but expecting "%s"',
                        $foundValue,
                        $variableName,
                        $expectedVariable['value']
                    ));
                }
            }
        }
    }

    /**
     * @Given pods are not running for the replication controller :name
     */
    public function podsAreNotRunningForTheReplicationController($name)
    {
        $pods = $this->podRepository->findByReplicationController($this->replicationControllerRepository->findOneByName($name));

        foreach ($pods as $pod) {
            $this->podRepository->delete($pod);
        }
    }

    /**
     * @Given pods are running for the replication controller :name
     */
    public function podsAreRunningForTheReplicationController($name)
    {
        $replicationController = $this->replicationControllerRepository->findOneByName($name);
        $pods = $this->podRepository->findByReplicationController($replicationController)->getPods();

        if (0 == count($pods)) {
            $selector = $replicationController->getSpecification()->getSelector();
            $counts = $replicationController->getSpecification()->getReplicas();

            for ($i = 0; $i < $counts; $i++) {
                $this->podRepository->create(new Pod(
                    new ObjectMetadata($name.'-'.$i, KeyValueObjectList::fromAssociativeArray($selector, Label::class)),
                    $replicationController->getSpecification()->getPodTemplateSpecification()->getPodSpecification()
                ));
            }
        }
    }

    /**
     * @param string $name
     * @param array  $collection
     *
     * @return ReplicationController[]
     */
    private function getReplicationControllersByName($name, array $collection)
    {
        return array_filter($collection, function (ReplicationController $replicationController) use ($name) {
            return $replicationController->getMetadata()->getName() == $name;
        });
    }

    /**
     * @param string $name
     * @param array  $collection
     *
     * @return ReplicationController
     */
    private function getReplicationControllerByName($name, array $collection)
    {
        $matchingRCs = $this->getReplicationControllersByName($name, $collection);
        if (count($matchingRCs) == 0) {
            throw new \RuntimeException(sprintf('Replication controller "%s" should be updated, not found in traces', $name));
        }

        return current($matchingRCs);
    }
}
