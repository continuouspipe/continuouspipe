<?php

namespace ContinuousPipe\River\ClusterPolicies\Environment;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\ClusterPolicies\ClusterResolution\ClusterPolicyResolver;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use Psr\Log\LoggerInterface;

class EnforceEnvironmentPolicyWhenDeploying implements DeploymentRequestFactory
{
    /**
     * @var DeploymentRequestFactory
     */
    private $decoratedFactory;

    /**
     * @var ClusterPolicyResolver
     */
    private $clusterPolicyResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(DeploymentRequestFactory $decoratedFactory, ClusterPolicyResolver $clusterPolicyResolver, LoggerInterface $logger)
    {
        $this->decoratedFactory = $decoratedFactory;
        $this->clusterPolicyResolver = $clusterPolicyResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $deploymentRequest = $this->decoratedFactory->create($tide, $taskDetails, $configuration);

        try {
            $policy = $this->clusterPolicyResolver->find($tide->getTeam(), $deploymentRequest->getTarget()->getClusterIdentifier(), 'environment');
        } catch (ClusterNotFound $e) {
            $this->logger->warning('Cannot load policies, cluster is not found', [
                'team' => $tide->getTeam()->getSlug(),
                'clusterIdentifier' => $deploymentRequest->getTarget()->getClusterIdentifier(),
                'tide' => $tide->getUuid()->toString(),
                'exception' => $e,
            ]);

            return $deploymentRequest;
        }

        if (null === $policy) {
            return $deploymentRequest;
        }

        $policyConfiguration = $policy->getConfiguration();
        if (isset($policyConfiguration['prefix'])) {
            if (0 !== strpos($deploymentRequest->getTarget()->getEnvironmentName(), $policyConfiguration['prefix'])) {
                throw new DeploymentRequestException(sprintf('Cluster policy enforces that environment name should be prefixed by %s', $policyConfiguration['prefix']));
            }
        }

        return $deploymentRequest;
    }
}
