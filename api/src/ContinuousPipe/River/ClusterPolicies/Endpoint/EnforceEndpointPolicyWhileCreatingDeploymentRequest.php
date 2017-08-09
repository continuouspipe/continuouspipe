<?php

namespace ContinuousPipe\River\ClusterPolicies\Endpoint;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\ClusterPolicies\ClusterPolicyException;
use ContinuousPipe\River\ClusterPolicies\ClusterResolution\ClusterPolicyResolver;
use ContinuousPipe\River\Task\Deploy\Configuration\Endpoint\HostnameResolver;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster\ClusterPolicy;
use Kubernetes\Client\Model\IngressRule;

class EnforceEndpointPolicyWhileCreatingDeploymentRequest implements DeploymentRequestFactory
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
     * @var HostnameResolver
     */
    private $hostnameResolver;

    public function __construct(
        DeploymentRequestFactory $decoratedFactory,
        ClusterPolicyResolver $clusterPolicyResolver,
        HostnameResolver $hostnameResolver
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->clusterPolicyResolver = $clusterPolicyResolver;
        $this->hostnameResolver = $hostnameResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $deploymentRequest = $this->decoratedFactory->create($tide, $taskDetails, $configuration);
        if (null === ($policy = $this->clusterPolicyResolver->find($tide->getTeam(), $deploymentRequest->getTarget()->getClusterIdentifier(), 'endpoint'))) {
            return $deploymentRequest;
        }

        return new DeploymentRequest(
            $deploymentRequest->getTarget(),
            new DeploymentRequest\Specification(array_map(function (Component $component) use ($tide, $policy) {
                return $this->enforcePolicyOnComponent($tide, $component, $policy);
            }, $deploymentRequest->getSpecification()->getComponents())),
            $deploymentRequest->getNotification(),
            $deploymentRequest->getCredentialsBucket()
        );
    }

    private function enforcePolicyOnComponent(Tide $tide, Component $component, ClusterPolicy $policy) : Component
    {
        if (null === ($specification = $component->getSpecification())) {
            return $component;
        }

        $policyConfiguration = $policy->getConfiguration();

        // Add deprecated accessibility compatibility
        if ($specification->getAccessibility()->isFromExternal()) {
            $specification->setAccessibility(new Component\Accessibility(
                $specification->getAccessibility()->isFromCluster(),
                false
            ));

            if (empty($component->getEndpoints())) {
                $component->setEndpoints([
                    new Component\Endpoint($component->getName()),
                ]);
            }
        }

        $component->setEndpoints(array_map(function (Component\Endpoint $endpoint) use ($tide, $policyConfiguration) {
            if (isset($policyConfiguration['type'])) {
                if (null === $endpoint->getType()) {
                    $endpoint->setType($policyConfiguration['type']);
                } elseif ($endpoint->getType() != $policyConfiguration['type']) {
                    throw new ClusterPolicyException(sprintf(
                        'Endpoint "%s" has a type "%s" while type "%s" is enforced by the cluster policy',
                        $endpoint->getName(),
                        $endpoint->getType(),
                        $policyConfiguration['type']
                    ));
                }
            }

            if ($endpoint->getType() == 'ingress' && null === $endpoint->getIngress()) {
                $endpoint->setIngress(new Component\Endpoint\EndpointIngress());
            }

            if (isset($policyConfiguration['ingress-class'])) {
                if (null !== ($ingress = $endpoint->getIngress())) {
                    if (null === $ingress->getClass()) {
                        $ingress->setClass($policyConfiguration['ingress-class']);
                    } elseif ($ingress->getClass() != $policyConfiguration['ingress-class']) {
                        throw new ClusterPolicyException(sprintf(
                            'Ingress class of component "%s" is "%s" while class "%s" is enforced by the cluster policy',
                            $endpoint->getName(),
                            $ingress->getClass(),
                            $policyConfiguration['ingress-class']
                        ));
                    }
                }
            }

            if (isset($policyConfiguration['ingress-host-suffix'])) {
                if (null !== ($ingress = $endpoint->getIngress())) {
                    if (empty($rules = $ingress->getRules())) {
                        $ingress->setRules([
                            $this->generateIngressRule($tide, '-'.$endpoint->getName().$policyConfiguration['ingress-host-suffix']),
                        ]);
                    }

                    foreach ($rules as $rule) {
                        if (substr($rule->getHost(), 0, -strlen($policyConfiguration['ingress-host-suffix'])) != $policyConfiguration['ingress-host-suffix']) {
                            throw new ClusterPolicyException(sprintf(
                                'Ingress hostname of component "%s" is "%s" while the suffix "%s" is enforced by the cluster policy',
                                $endpoint->getName(),
                                $rule->getHost(),
                                $policyConfiguration['ingress-host-suffix']
                            ));
                        }
                    }
                }
            }

            return $endpoint;
        }, $component->getEndpoints()));

        return $component;
    }

    private function generateIngressRule(Tide $tide, string $hostSuffix)
    {
        return new IngressRule(
            $this->hostnameResolver->resolveHostname(
                $tide->getFlowUuid(),
                $tide->getCodeReference(),
                $this->hostnameResolver->generateHostExpression($hostSuffix)
            )
        );
    }
}
