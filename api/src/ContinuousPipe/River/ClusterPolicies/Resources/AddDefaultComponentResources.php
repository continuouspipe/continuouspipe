<?php

namespace ContinuousPipe\River\ClusterPolicies\Resources;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\ClusterPolicies\ClusterResolution\ClusterPolicyResolver;
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
            if (null === $component->getSpecification()->getResources()) {
                $component->getSpecification()->setResources(new Component\Resources(
                    new Component\ResourcesRequest(
                        $policyConfiguration['default-cpu-request'] ?? null,
                        $policyConfiguration['default-memory-request'] ?? null
                    ),
                    new Component\ResourcesRequest(
                        $policyConfiguration['default-cpu-limit'] ?? null,
                        $policyConfiguration['default-memory-limit'] ?? null
                    )
                ));
            }
        }, $request->getSpecification()->getComponents());

        return $request;
    }
}
