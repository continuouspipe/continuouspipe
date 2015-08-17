<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableReplicationControllerRepository;
use Kubernetes\Client\Exception\ReplicationControllerNotFound;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\ReplicationController;

class ReplicationControllerContext implements Context
{
    /**
     * @var TraceableReplicationControllerRepository
     */
    private $replicationControllerRepository;

    /**
     * @param TraceableReplicationControllerRepository $replicationControllerRepository
     */
    public function __construct(TraceableReplicationControllerRepository $replicationControllerRepository)
    {
        $this->replicationControllerRepository = $replicationControllerRepository;
    }

    /**
     * @Then the replication controller :name should be created
     */
    public function theReplicationControllerShouldBeCreated($name)
    {
        $matchingRCs = array_filter($this->replicationControllerRepository->getCreatedReplicationControllers(), function(ReplicationController $replicationController) use ($name) {
            return $replicationController->getMetadata()->getName() == $name;
        });

        if (count($matchingRCs) == 0) {
            throw new \RuntimeException(sprintf('Replication controller "%s" should be created, not found in traces', $name));
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
            $this->replicationControllerRepository->create(new ReplicationController(new ObjectMetadata($name)));
        }
    }

    /**
     * @Then the replication controller :name shouldn't be updated
     */
    public function theReplicationControllerShouldnTBeUpdated($name)
    {
        $matchingRCs = array_filter($this->replicationControllerRepository->getUpdatedReplicationControllers(), function(ReplicationController $replicationController) use ($name) {
            return $replicationController->getMetadata()->getName() == $name;
        });

        if (count($matchingRCs) != 0) {
            throw new \RuntimeException(sprintf('Replication controller "%s" should NOT be updated, found in traces', $name));
        }
    }

    /**
     * @Then the replication controller :name should be updated
     */
    public function theReplicationControllerShouldBeUpdated($name)
    {
        $matchingRCs = array_filter($this->replicationControllerRepository->getUpdatedReplicationControllers(), function(ReplicationController $replicationController) use ($name) {
            return $replicationController->getMetadata()->getName() == $name;
        });

        if (count($matchingRCs) == 0) {
            throw new \RuntimeException(sprintf('Replication controller "%s" should be updated, not found in traces', $name));
        }
    }
}
