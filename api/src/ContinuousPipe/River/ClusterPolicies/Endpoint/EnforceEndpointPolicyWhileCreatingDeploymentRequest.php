<?php

namespace ContinuousPipe\River\ClusterPolicies\Endpoint;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\ClusterPolicies\ClusterPolicyException;
use ContinuousPipe\River\ClusterPolicies\ClusterResolution\ClusterPolicyResolver;
use ContinuousPipe\River\Task\Deploy\Configuration\Endpoint\HostnameResolver;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestException;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster\ClusterPolicy;
use Kubernetes\Client\Model\IngressHttpRule;
use Kubernetes\Client\Model\IngressRule;
use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DeploymentRequestFactory $decoratedFactory,
        ClusterPolicyResolver $clusterPolicyResolver,
        HostnameResolver $hostnameResolver,
        LoggerInterface $logger
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->clusterPolicyResolver = $clusterPolicyResolver;
        $this->hostnameResolver = $hostnameResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $deploymentRequest = $this->decoratedFactory->create($tide, $taskDetails, $configuration);

        try {
            $policy = $this->clusterPolicyResolver->find($tide->getTeam(), $deploymentRequest->getTarget()->getClusterIdentifier(), 'endpoint');
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

        try {
            return new DeploymentRequest(
                $deploymentRequest->getTarget(),
                new DeploymentRequest\Specification(array_map(function (Component $component) use ($tide, $policy) {
                    return $this->enforcePolicyOnComponent($tide, $component, $policy);
                }, $deploymentRequest->getSpecification()->getComponents())),
                $deploymentRequest->getNotification(),
                $deploymentRequest->getCredentialsBucket()
            );
        } catch (ClusterPolicyException $e) {
            throw new DeploymentRequestException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function enforcePolicyOnComponent(Tide $tide, Component $component, ClusterPolicy $policy) : Component
    {
        if (null === ($specification = $component->getSpecification())) {
            return $component;
        }

        $policyConfiguration = $policy->getConfiguration();
        $policySecrets = $policy->getSecrets();

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

        $component->setEndpoints(array_map(function (Component\Endpoint $endpoint) use ($tide, $policyConfiguration, $policySecrets) {
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

            if (isset($policyConfiguration['cloudflare-by-default'])) {
                if (null === $endpoint->getCloudFlareZone()) {
                    if (null === ($ingress = $endpoint->getIngress()) || 0 === count($ingress->getRules())) {
                        throw new ClusterPolicyException('Cannot apply the CloudFlare by default rule without ingress hostname');
                    }

                    $endpoint->setCloudFlareZone(new Component\Endpoint\CloudFlareZone(
                        $policySecrets['cloudflare-zone-identifier'],
                        new Component\Endpoint\CloudFlareAuthentication(
                            $policySecrets['cloudflare-email'],
                            $policySecrets['cloudflare-api-key']
                        ),
                        $endpoint->getIngress()->getRules()[0]->getHost(),
                        null,
                        null,
                        isset($policyConfiguration['cloudflare-proxied-by-default']) ? $policyConfiguration['cloudflare-proxied-by-default'] == 'true' : null,
                        null
                    ));
                }
            }

            if (isset($policyConfiguration['ssl-certificate-defaults']) && 'true' == $policyConfiguration['ssl-certificate-defaults']) {
                if (empty($endpoint->getSslCertificates())) {
                    $endpoint->setSslCertificates([
                        new Component\Endpoint\SslCertificate($endpoint->getName(), $policySecrets['ssl-certificate-cert'], $policySecrets['ssl-certificate-key']),
                    ]);
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
            ),
            new IngressHttpRule([])
        );
    }
}
