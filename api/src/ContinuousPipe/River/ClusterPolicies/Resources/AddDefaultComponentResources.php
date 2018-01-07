<?php

namespace ContinuousPipe\River\ClusterPolicies\Resources;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\River\ClusterPolicies\ClusterResolution\ClusterPolicyResolver;
use ContinuousPipe\River\Managed\Resources\Calculation\ResourceConverter;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Pipe\DeploymentRequestEnhancer\DeploymentRequestEnhancer;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use Psr\Log\LoggerInterface;

class AddDefaultComponentResources implements DeploymentRequestEnhancer
{
    /**
     * @var DeploymentRequestEnhancer
     */
    private $decoratedEnhancer;

    /**
     * @var ClusterPolicyResolver
     */
    private $clusterPolicyResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeploymentRequestEnhancer $decoratedEnhancer
     * @param ClusterPolicyResolver $clusterPolicyResolver
     * @param LoggerInterface $logger
     */
    public function __construct(DeploymentRequestEnhancer $decoratedEnhancer, ClusterPolicyResolver $clusterPolicyResolver, LoggerInterface $logger)
    {
        $this->decoratedEnhancer = $decoratedEnhancer;
        $this->clusterPolicyResolver = $clusterPolicyResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Tide $tide, TaskDetails $taskDetails, DeploymentRequest $deploymentRequest)
    {
        $request = $this->decoratedEnhancer->enhance($tide, $taskDetails, $deploymentRequest);

        try {
            $policy = $this->clusterPolicyResolver->find($tide->getTeam(), $deploymentRequest->getTarget()->getClusterIdentifier(), 'resources');
        } catch (ClusterNotFound $e) {
            $this->logger->warning('Cannot load policies, cluster is not found', [
                'team' => $tide->getTeam()->getSlug(),
                'clusterIdentifier' => $deploymentRequest->getTarget()->getClusterIdentifier(),
                'tide' => $tide->getUuid()->toString(),
                'exception' => $e,
            ]);

            return $request;
        }

        if (null === $policy) {
            return $request;
        }

        $policyConfiguration = $policy->getConfiguration();
        array_map(function (Component $component) use ($policyConfiguration) {
            if (null === ($resources = $component->getSpecification()->getResources())) {
                $resources = new Component\Resources();
            }

            $requests = $this->resourcesWithDefaults($resources->getRequests(), $policyConfiguration['default-cpu-request'] ?? null, $policyConfiguration['default-memory-request'] ?? null);
            $limits = $this->resourcesWithDefaults($resources->getLimits(), $policyConfiguration['default-cpu-limit'] ?? null, $policyConfiguration['default-memory-limit'] ?? null);

            if ('true' === ($policyConfiguration['cpu-requests-and-limits-have-to-be-equals'] ?? '')) {
                $this->assertResourcesEquals($component, $resources->getLimits()->getCpu(), $resources->getRequests()->getCpu(), 'Component "%s" need to have CPU limits (got "%s") matching CPU requests (got "%s")');
            }
            if ('true' === ($policyConfiguration['memory-requests-and-limits-have-to-be-equals'] ?? '')) {
                $this->assertResourcesEquals($component, $resources->getLimits()->getMemory(), $resources->getRequests()->getMemory(), 'Component "%s" need to have memory limits (got "%s") matching memory requests (got "%s")');
            }

            isset($policyConfiguration['max-cpu-request']) && $this->assertResourceLessThan($component, $requests->getCpu(), $policyConfiguration['max-cpu-request'], 'Component "%s" has a requested "%s" CPU while "%s" is enforced by the cluster policy');
            isset($policyConfiguration['max-cpu-limit']) && $this->assertResourceLessThan($component, $limits->getCpu(), $policyConfiguration['max-cpu-limit'], 'Component "%s" has a requested a limit of "%s" CPU while "%s" is enforced by the cluster policy');
            isset($policyConfiguration['max-memory-request']) && $this->assertResourceLessThan($component, $requests->getMemory(), $policyConfiguration['max-memory-request'], 'Component "%s" has a requested "%s" of memory while "%s" is enforced by the cluster policy');
            isset($policyConfiguration['max-memory-limit']) && $this->assertResourceLessThan($component, $limits->getMemory(), $policyConfiguration['max-memory-limit'], 'Component "%s" has a requested a limit of "%s" of memory while "%s" is enforced by the cluster policy');

            $component->getSpecification()->setResources(new Component\Resources($requests, $limits));
        }, $request->getSpecification()->getComponents());

        return $request;
    }

    private function resourcesWithDefaults(Component\ResourcesRequest $resources = null, $defaultCpu = null, $defaultMemory = null)
    {
        if (null === $resources) {
            $resources = new Component\ResourcesRequest();
        }

        if (!$resources->getCpu()) {
            $resources = new Component\ResourcesRequest($defaultCpu, $resources->getMemory());
        }

        if (!$resources->getMemory()) {
            $resources = new Component\ResourcesRequest($resources->getCpu(), $defaultMemory);
        }

        return $resources;
    }

    private function assertResourcesEquals(Component $component, $limit, $request, $exceptionMessage)
    {
        if (!$this->resourcesEquals($limit, $request)) {
            throw new DeploymentRequestException(sprintf(
                $exceptionMessage,
                $component->getName(),
                $limit,
                $request
            ));
        }
    }

    private function assertResourceLessThan(Component $component, string $value, string $maximum, string $exceptionMessage)
    {
        if ($this->resourceGreaterThan($value, $maximum)) {
            throw new DeploymentRequestException(sprintf(
                $exceptionMessage,
                $component->getName(),
                $value,
                $maximum
            ));
        }
    }

    private function resourceGreaterThan(string $value, string $compareTo) : bool
    {
        return ResourceConverter::resourceToNumber($value) > ResourceConverter::resourceToNumber($compareTo);
    }

    private function resourcesEquals($value, $compareTo) : bool
    {
        if ($value == null && $compareTo == null) {
            return true;
        }

        if ($value == null || $compareTo == null) {
            return false;
        }

        return ResourceConverter::resourceToNumber($value) == ResourceConverter::resourceToNumber($compareTo);
    }
}
