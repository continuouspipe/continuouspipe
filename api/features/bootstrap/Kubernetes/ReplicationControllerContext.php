<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\InMemoryPodRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableReplicationControllerRepository;
use Kubernetes\Client\Exception\ReplicationControllerNotFound;
use Kubernetes\Client\Model\ContainerStatus;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\Label;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodSpecification;
use Kubernetes\Client\Model\PodStatus;
use Kubernetes\Client\Model\PodStatusCondition;
use Kubernetes\Client\Model\PodTemplateSpecification;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\ReplicationControllerSpecification;
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
     * @var InMemoryPodRepository
     */
    private $inMemoryPodRepository;

    /**
     * @param TraceableReplicationControllerRepository $replicationControllerRepository
     * @param InMemoryPodRepository $inMemoryPodRepository
     * @param PodRepository $podRepository
     */
    public function __construct(TraceableReplicationControllerRepository $replicationControllerRepository, InMemoryPodRepository $inMemoryPodRepository, PodRepository $podRepository)
    {
        $this->replicationControllerRepository = $replicationControllerRepository;
        $this->inMemoryPodRepository = $inMemoryPodRepository;
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
     * @Then the replication controller :name should be deleted
     */
    public function theReplicationControllerShouldBeDeleted($name)
    {
        $this->getReplicationControllerByName($name, $this->replicationControllerRepository->getDeletedReplicationControllers());
    }

    /**
     * @Then the replication controller :name should not be deleted
     */
    public function theReplicationControllerShouldNotBeDeleted($name)
    {
        try {
            $this->getReplicationControllerByName($name, $this->replicationControllerRepository->getDeletedReplicationControllers());
            $found = true;
        } catch (\RuntimeException $e) {
            $found = false;
        }

        if ($found) {
            throw new \RuntimeException('Replication controller found in the list of deleted');
        }
    }

    /**
     * @Given I have an existing replication controller :name
     */
    public function iHaveAnExistingReplicationController($name)
    {
        try {
            $this->replicationControllerRepository->findOneByName($name);
        } catch (ReplicationControllerNotFound $e) {
            $this->replicationControllerRepository->create(new ReplicationController(
                new ObjectMetadata($name),
                new ReplicationControllerSpecification(1, [],
                    new PodTemplateSpecification(
                        new ObjectMetadata($name),
                        new PodSpecification([], [])
                    )
                )
            ));
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
     * @Then the replication controller :name should not be created
     */
    public function theReplicationControllerShouldNotBeCreated($name)
    {
        $matchingRCs = $this->getReplicationControllersByName($name, $this->replicationControllerRepository->getCreatedReplicationControllers());

        if (count($matchingRCs) != 0) {
            throw new \RuntimeException(sprintf('Replication controller "%s" should NOT be created but found in traces', $name));
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

        if (0 === count($containers)) {
            throw new \RuntimeException('No container found');
        }

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
        $this->podsAreNotRunningForTheReplicationController($name);

        $replicationController = $this->replicationControllerRepository->findOneByName($name);

        $selector = $replicationController->getSpecification()->getSelector();
        $counts = $replicationController->getSpecification()->getReplicas();

        for ($i = 0; $i < $counts; $i++) {
            $this->inMemoryPodRepository->create(new Pod(
                new ObjectMetadata($name.'-'.$i, KeyValueObjectList::fromAssociativeArray($selector, Label::class)),
                $replicationController->getSpecification()->getPodTemplateSpecification()->getPodSpecification(),
                new PodStatus(PodStatus::PHASE_RUNNING, '10.240.162.87', '10.132.1.47', [
                    new PodStatusCondition('Ready', true)
                ], [
                    new ContainerStatus($name, 1, 'docker://ec0041d2f4d9ad598ce6dae9146e351ac1e315da944522d1ca140c5d2cafd97e', null, true)
                ])
            ));
        }
    }

    /**
     * @Given pods are running but not ready for the replication controller :name
     */
    public function podsAreRunningButNotReadyForTheReplicationController($name)
    {
        $this->podsAreNotRunningForTheReplicationController($name);

        $replicationController = $this->replicationControllerRepository->findOneByName($name);
        $selector = $replicationController->getSpecification()->getSelector();
        $counts = $replicationController->getSpecification()->getReplicas();

        for ($i = 0; $i < $counts; $i++) {
            $this->inMemoryPodRepository->create(new Pod(
                new ObjectMetadata($name.'-'.$i, KeyValueObjectList::fromAssociativeArray($selector, Label::class)),
                $replicationController->getSpecification()->getPodTemplateSpecification()->getPodSpecification(),
                new PodStatus(PodStatus::PHASE_RUNNING, '10.240.162.87', '10.132.1.47', [
                    new PodStatusCondition('Ready', false)
                ], [
                    new ContainerStatus($name, 13, 'docker://ec0041d2f4d9ad598ce6dae9146e351ac1e315da944522d1ca140c5d2cafd97e', null, false)
                ])
            ));
        }
    }

    /**
     * @Given pods are pending for the replication controller :name
     */
    public function podsArePendingForTheReplicationController($name)
    {
        $this->podsAreNotRunningForTheReplicationController($name);

        $replicationController = $this->replicationControllerRepository->findOneByName($name);
        $selector = $replicationController->getSpecification()->getSelector();
        $counts = $replicationController->getSpecification()->getReplicas();

        for ($i = 0; $i < $counts; $i++) {
            $this->inMemoryPodRepository->create(new Pod(
                new ObjectMetadata($name.'-'.$i, KeyValueObjectList::fromAssociativeArray($selector, Label::class)),
                $replicationController->getSpecification()->getPodTemplateSpecification()->getPodSpecification(),
                new PodStatus(PodStatus::PHASE_PENDING, '1.2.3.4', null, [], [])
            ));
        }
    }

    /**
     * @Then at least one pod of the replication controller :name should be running
     */
    public function atLeastOnePodOfTheReplicationControllerShouldBeRunning($name)
    {
        $replicationController = $this->replicationControllerRepository->findOneByName($name);
        $pods = $this->podRepository->findByReplicationController($replicationController)->getPods();

        if (0 === count($pods)) {
            throw new \RuntimeException('No pod found');
        }

        $runningPods = array_filter($pods, function(Pod $pod) {
            return $pod->getStatus()->getPhase() == PodStatus::PHASE_RUNNING;
        });

        if (0 === count($runningPods)) {
            throw new \RuntimeException('No running pod found');
        }
    }

    /**
     * @Then the component :componentName should be deployed with the command :expectedCommand
     */
    public function theComponentShouldBeDeployedWithTheCommand($componentName, $expectedCommand)
    {
        $replicationController = $this->replicationControllerRepository->findOneByName($componentName);
        $pods = $this->podRepository->findByReplicationController($replicationController)->getPods();

        if (0 == count($pods)) {
            throw new \RuntimeException('No pods found');
        }

        foreach ($pods as $pod) {
            $container = $pod->getSpecification()->getContainers()[0];
            if (null == ($command = $container->getCommand())) {
                throw new \RuntimeException('Found null command');
            }

            if (!in_array($expectedCommand, $command)) {
                throw new \RuntimeException(sprintf(
                    'Command "%s" not found is "%s"',
                    $expectedCommand,
                    implode(' ', $command)
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
            throw new \RuntimeException(sprintf('Replication controller "%s" not found in traces', $name));
        }

        return current($matchingRCs);
    }
}
