<?php

namespace Kubernetes\Mock;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookablePodRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookableReplicationControllerRepository;
use Kubernetes\Client\Model\ContainerStatus;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodList;
use Kubernetes\Client\Model\PodStatus;
use Kubernetes\Client\Model\PodStatusCondition;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Exception\ReplicationControllerNotFound;

class ReplicationControllerContext implements Context
{
    /**
     * @var \Kubernetes\ReplicationControllerContext
     */
    private $replicationControllerContext;

    /**
     * @var HookableReplicationControllerRepository
     */
    private $hookableReplicationControllerRepository;

    /**
     * @var HookablePodRepository
     */
    private $hookablePodRepository;

    /**
     * @param HookableReplicationControllerRepository $hookableReplicationControllerRepository
     * @param HookablePodRepository $hookablePodRepository
     */
    public function __construct(HookableReplicationControllerRepository $hookableReplicationControllerRepository, HookablePodRepository $hookablePodRepository)
    {
        $this->hookableReplicationControllerRepository = $hookableReplicationControllerRepository;
        $this->hookablePodRepository = $hookablePodRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->replicationControllerContext = $scope->getEnvironment()->getContext('Kubernetes\ReplicationControllerContext');
    }

    /**
     * @Given the pods of the replication controllers will be created successfully and running
     */
    public function thePodsOfTheReplicationControllersWillBeCreatedSuccessfullyAndRunning()
    {
        $hook = function(ReplicationController $replicationController) {
            $name = $replicationController->getMetadata()->getName();

            $this->replicationControllerContext->podsAreRunningForTheReplicationController($name);

            return $replicationController;
        };

        $this->hookableReplicationControllerRepository->addCreatedHook($hook);
        $this->hookableReplicationControllerRepository->addUpdatedHook($hook);

        $this->hookablePodRepository->addDeletedHook(function(Pod $pod) {
            $labels = $pod->getMetadata()->getLabelsAsAssociativeArray();

            try {
                $replicationController = $this->hookableReplicationControllerRepository->findOneByLabels($labels);
                $this->hookablePodRepository->create($pod);
            } catch (ReplicationControllerNotFound $e) {
                // If no replication controller is matching, then don't mind...
            }

            return $pod;
        });
    }

    /**
     * @Given the pods of the replication controller :name will be pending after creation
     */
    public function thePodsOfTheReplicationControllerWillBePendingAfterCreation($name)
    {
        $this->hookableReplicationControllerRepository->addCreatedHook(function(ReplicationController $replicationController) use ($name) {
            if ($replicationController->getMetadata()->getName() != $name) {
                return $replicationController;
            }

            $this->replicationControllerContext->podsArePendingForTheReplicationController($name);

            return $replicationController;
        });
    }

    /**
     * @Given the pods of the replication controller :name will be running after creation
     */
    public function thePodsOfTheReplicationControllerWillBeRunningAfterCreation($name)
    {
        $this->hookableReplicationControllerRepository->addCreatedHook(function(ReplicationController $replicationController) use ($name) {
            if ($replicationController->getMetadata()->getName() != $name) {
                return $replicationController;
            }

            $this->replicationControllerContext->podsAreRunningForTheReplicationController($name);

            return $replicationController;
        });
    }

    /**
     * @Given the pods of the replication controller :name will become running later
     */
    public function thePodsOfTheReplicationControllerWillBecomeRunningLater($name)
    {
        $callCount = 0;
        $this->hookablePodRepository->addFoundByReplicationControllerHook(function(ReplicationController $replicationController, PodList $pods) use ($name, &$callCount) {
            if ($replicationController->getMetadata()->getName() != $name) {
                return $pods;
            }

            // Return running pods only after a set of calls
            if ($callCount++ < 10) {
                return $pods;
            }

            $runningPods = array_map(function(Pod $pod) {
                return new Pod(
                    $pod->getMetadata(),
                    $pod->getSpecification(),
                    new PodStatus(PodStatus::PHASE_RUNNING, '10.240.162.87', '10.132.1.47', [
                        new PodStatusCondition('Ready', true)
                    ], [
                        new ContainerStatus($pod->getMetadata()->getName(), 1, 'docker://ec0041d2f4d9ad598ce6dae9146e351ac1e315da944522d1ca140c5d2cafd97e', null, false)
                    ])
                );
            }, $pods->getPods());

            return PodList::fromPods($runningPods);
        });
    }
}
