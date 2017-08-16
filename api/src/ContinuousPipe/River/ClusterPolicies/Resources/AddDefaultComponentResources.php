<?php

namespace ContinuousPipe\River\ClusterPolicies\Resources;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\ClusterPolicies\ClusterResolution\ClusterPolicyResolver;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Pipe\DeploymentRequestEnhancer\DeploymentRequestEnhancer;
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
    public function enhance(Tide $tide, DeploymentRequest $deploymentRequest)
    {
        $request = $this->decoratedEnhancer->enhance($tide, $deploymentRequest);

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

        $policyConfiguration = $policy->getConfiguration();
        array_map(function (Component $component) use ($policyConfiguration) {
            if (null === ($resources = $component->getSpecification()->getResources())) {
                $resources = new Component\Resources();
            }

            if (null === ($requests = $resources->getRequests())) {
                $requests = new Component\ResourcesRequest(
                    $policyConfiguration['default-cpu-request'] ?? null,
                    $policyConfiguration['default-memory-request'] ?? null
                );
            }

            if (null === ($limits = $resources->getLimits())) {
                $limits = new Component\ResourcesRequest(
                    $policyConfiguration['default-cpu-limit'] ?? null,
                    $policyConfiguration['default-memory-limit'] ?? null
                );
            }

            isset($policyConfiguration['max-cpu-request']) && $this->assertResourceLessThan($component, $requests->getCpu(), $policyConfiguration['max-cpu-request'], 'Component "%s" has a requested "%s" CPU while "%s" is enforced by the cluster policy');
            isset($policyConfiguration['max-cpu-limit']) && $this->assertResourceLessThan($component, $limits->getCpu(), $policyConfiguration['max-cpu-limit'], 'Component "%s" has a requested a limit of "%s" CPU while "%s" is enforced by the cluster policy');
            isset($policyConfiguration['max-memory-request']) && $this->assertResourceLessThan($component, $requests->getMemory(), $policyConfiguration['max-memory-request'], 'Component "%s" has a requested "%s" of memory while "%s" is enforced by the cluster policy');
            isset($policyConfiguration['max-memory-limit']) && $this->assertResourceLessThan($component, $limits->getMemory(), $policyConfiguration['max-memory-limit'], 'Component "%s" has a requested a limit of "%s" of memory while "%s" is enforced by the cluster policy');

            $component->getSpecification()->setResources(new Component\Resources($requests, $limits));
        }, $request->getSpecification()->getComponents());

        return $request;
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

    private function resourceGreaterThan(string $value, string $compareTo)
    {
        return ResourceConverter::resourceToNumber($value) > ResourceConverter::resourceToNumber($compareTo);
    }
}
