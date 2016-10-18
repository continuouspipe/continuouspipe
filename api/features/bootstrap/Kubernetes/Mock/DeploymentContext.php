<?php

namespace Kubernetes\Mock;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookableDeploymentRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookablePodRepository;
use Kubernetes\Client\Exception\DeploymentNotFound;
use Kubernetes\Client\Model\ContainerStatus;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\DeploymentStatus;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\Label;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodList;
use Kubernetes\Client\Model\PodStatus;
use Kubernetes\Client\Model\PodStatusCondition;
use Kubernetes\Client\Repository\DeploymentRepository;
use Kubernetes\Client\Repository\PodRepository;

class DeploymentContext implements Context
{
    /**
     * @var HookableDeploymentRepository
     */
    private $hookableDeploymentRepository;

    /**
     * @var HookablePodRepository
     */
    private $hookablePodRepository;
    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;
    /**
     * @var PodRepository
     */
    private $podRepository;

    /**
     * @param HookableDeploymentRepository $hookableDeploymentRepository
     * @param HookablePodRepository $hookablePodRepository
     * @param DeploymentRepository $deploymentRepository
     * @param PodRepository $podRepository
     */
    public function __construct(HookableDeploymentRepository $hookableDeploymentRepository, HookablePodRepository $hookablePodRepository, DeploymentRepository $deploymentRepository, PodRepository $podRepository)
    {
        $this->hookableDeploymentRepository = $hookableDeploymentRepository;
        $this->hookablePodRepository = $hookablePodRepository;
        $this->deploymentRepository = $deploymentRepository;
        $this->podRepository = $podRepository;
    }

    /**
     * @Given the pods of the deployment :deploymentName will be :phase after creation
     */
    public function thePodsOfTheDeploymentWillBePendingAfterCreation($deploymentName, $phase)
    {
        $this->hookableDeploymentRepository->addCreatedHook(function(Deployment $deployment) use ($deploymentName, $phase) {
            if ($deployment->getMetadata()->getName() != $deploymentName) {
                return $deployment;
            }

            if ($phase == 'pending') {
                $podStatus = PodStatus::PHASE_PENDING;
            } elseif ('running' == $phase) {
                $podStatus = PodStatus::PHASE_RUNNING;
            } else {
                throw new \RuntimeException(sprintf('Unknown phase "%s"', $phase));
            }

            return $this->podsAreForTheDeployment($deployment, $podStatus);
        });
    }

    /**
     * @Given the pods of the deployment :deploymentName will become running later
     * @Given the pods of the deployments will be running after creation
     */
    public function thePodsOfTheDeploymentWillBecomeRunningLater($deploymentName = null)
    {
        $callCount = 0;
        $this->hookableDeploymentRepository->addFoundByNameHook(function($name, Deployment $deployment) use ($deploymentName, &$callCount) {
            if (null !== $deploymentName && $name != $deploymentName) {
                return $deployment;
            }

            // Return running pods only after a set of calls
            if ($callCount++ < 10) {
                return $deployment;
            }

            $replicas = $deployment->getSpecification()->getReplicas();
            $status = new DeploymentStatus(1, $replicas, $replicas, $replicas, 0);
            $deployment = new Deployment($deployment->getMetadata(), $deployment->getSpecification(), $status);

            $this->deploymentRepository->update($deployment);

            return $deployment;
        });

        $this->hookablePodRepository->addFoundByLabelsHook(function(array $labels, PodList $pods) use ($deploymentName, &$callCount) {
            // If the deployment is not found, do nothing
            try {
                $deployment = $this->deploymentRepository->findOneByName($deploymentName);
            } catch (DeploymentNotFound $e) {
                return $pods;
            }

            // If the selector is different, then do nothing
            if ($labels != $deployment->getSpecification()->getSelector()) {
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
                        new ContainerStatus($pod->getMetadata()->getName(), 1, 'docker://ec0041d2f4d9ad598ce6dae9146e351ac1e315da944522d1ca140c5d2cafd97e', null, true)
                    ])
                );
            }, $pods->getPods());

            return PodList::fromPods($runningPods);
        });
    }

    /**
     * @Given pods are :status for the deployment :name
     */
    public function podsArePendingForTheDeployment($status, $name)
    {
        $this->podsAreForTheDeployment(
            $this->deploymentRepository->findOneByName($name),
            $status,
            $status == 'running'
        );
    }

    /**
     * @param Deployment $deployment
     * @param string $podStatus
     *
     * @return Deployment
     */
    private function podsAreForTheDeployment(Deployment $deployment, $podStatus, $ready = true)
    {
        $this->removeDeploymentsPods($deployment);

        $name = $deployment->getMetadata()->getName();
        $selector = $deployment->getSpecification()->getSelector();
        $counts = $deployment->getSpecification()->getReplicas();

        for ($i = 0; $i < $counts; $i++) {
            $status = new PodStatus($podStatus, '10.240.162.87', '10.132.1.47', [
                new PodStatusCondition('Ready', true)
            ], [
                new ContainerStatus($name, 1, 'docker://ec0041d2f4d9ad598ce6dae9146e351ac1e315da944522d1ca140c5d2cafd97e', null, $ready)
            ]);

            $this->podRepository->create(new Pod(
                new ObjectMetadata($name.'-'.$i, KeyValueObjectList::fromAssociativeArray($selector, Label::class)),
                $deployment->getSpecification()->getTemplate()->getPodSpecification(),
                $status
            ));
        }

        $available = $podStatus == PodStatus::PHASE_RUNNING;
        $status = new DeploymentStatus(1, $counts, $counts, $available ? $counts : 0, $available ? 0 : $counts);

        if ($status !== $deployment->getStatus()) {
            $deployment = new Deployment($deployment->getMetadata(), $deployment->getSpecification(), $status);

            $this->deploymentRepository->update($deployment);
        }

        return $deployment;
    }

    /**
     * @param Deployment $deployment
     */
    public function removeDeploymentsPods(Deployment $deployment)
    {
        $selector = $deployment->getSpecification()->getSelector();
        $pods = $this->podRepository->findByLabels($selector);

        foreach ($pods as $pod) {
            $this->podRepository->delete($pod);
        }
    }
}
