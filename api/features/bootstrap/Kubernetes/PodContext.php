<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookablePodRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\InMemoryPodRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceablePodRepository;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\Label;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodSpecification;
use Kubernetes\Client\Model\PodStatus;

class PodContext implements Context
{
    /**
     * @var TraceablePodRepository
     */
    private $podRepository;

    /**
     * @var InMemoryPodRepository
     */
    private $inMemoryPodRepository;
    /**
     * @var HookablePodRepository
     */
    private $hookablePodRepository;

    /**
     * @param TraceablePodRepository $podRepository
     * @param InMemoryPodRepository $inMemoryPodRepository
     * @param HookablePodRepository $hookablePodRepository
     */
    public function __construct(TraceablePodRepository $podRepository, InMemoryPodRepository $inMemoryPodRepository, HookablePodRepository $hookablePodRepository)
    {
        $this->podRepository = $podRepository;
        $this->inMemoryPodRepository = $inMemoryPodRepository;
        $this->hookablePodRepository = $hookablePodRepository;
    }

    /**
     * @Given there is a pod named :name labelled :labelsString
     */
    public function thereIsAPodNamedLabelled($name, $labelsString)
    {
        $labels = [];
        foreach (explode(',', $labelsString)  as $labelString) {
            list($key, $value) = explode('=', $labelString);

            $labels[] = new Label($key, $value);
        }

        $this->inMemoryPodRepository->create(new Pod(
            new ObjectMetadata($name, new KeyValueObjectList($labels)),
            new PodSpecification(
                []
            )
        ));
    }

    /**
     * @Given the pod :podName will fail with exit code :exitCode
     */
    public function thePodWillFailWithExitCode($podName, $exitCode)
    {
        $calls = 0;
        $this->inMemoryPodRepository->setAttachCallback(function(Pod $pod, callable $callable) use ($podName, &$calls) {
            if ($pod->getMetadata()->getName() != $podName) {
                return $pod;
            }

            return new Pod($pod->getMetadata(), $pod->getSpecification(), new PodStatus(
                PodStatus::PHASE_FAILED,
                null,
                null,
                [],
                []
            ));
        });
    }

    /**
     * @Given the pod :podName will run successfully
     */
    public function thePodWillRunSuccessfully($podName)
    {
        $this->hookablePodRepository->addCreatedHook(function(Pod $pod) {
            return $this->inMemoryPodRepository->update(new Pod($pod->getMetadata(), $pod->getSpecification(), new PodStatus(
                PodStatus::PHASE_SUCCEEDED,
                null,
                null,
                [],
                []
            )));
        });

        $this->inMemoryPodRepository->setAttachCallback(function(Pod $pod, callable $callable) use ($podName, &$calls) {
            if ($pod->getMetadata()->getName() != $podName) {
                return $pod;
            }

            return new Pod($pod->getMetadata(), $pod->getSpecification(), new PodStatus(
                PodStatus::PHASE_SUCCEEDED,
                null,
                null,
                [],
                []
            ));
        });
    }

    /**
     * @Then the pod :podName should be created
     */
    public function thePodShouldBeCreated($podName)
    {
        $matchingCreated = array_filter($this->podRepository->getCreated(), function(Pod $pod) use ($podName) {
            return $pod->getMetadata()->getName() == $podName;
        });

        if (0 == count($matchingCreated)) {
            throw new \RuntimeException(sprintf(
                'No created pod named "%s"',
                $podName
            ));
        }
    }

    /**
     * @Then the pod :podName should be deleted
     */
    public function thePodShouldBeDeleted($podName)
    {
        $matchingDeleted = array_filter($this->podRepository->getDeleted(), function(Pod $pod) use ($podName) {
            return $pod->getMetadata()->getName() == $podName;
        });

        if (0 == count($matchingDeleted)) {
            throw new \RuntimeException(sprintf(
                'No deleted pod named "%s"',
                $podName
            ));
        }
    }

    /**
     * @Then the pod :podName should not be deleted
     */
    public function thePodShouldNotBeDeleted($podName)
    {
        $matchingDeleted = array_filter($this->podRepository->getDeleted(), function(Pod $pod) use ($podName) {
            return $pod->getMetadata()->getName() == $podName;
        });

        if (0 != count($matchingDeleted)) {
            throw new \RuntimeException(sprintf(
                'Deleted pod named "%s"',
                $podName
            ));
        }
    }
}
