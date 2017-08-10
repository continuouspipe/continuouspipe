<?php


namespace ContinuousPipe\River\SslCertificate\Generation;


use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\SslCertificate\SslCertificateException;
use ContinuousPipe\River\SslCertificate\SslCertificateManager;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestException;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use Kubernetes\Client\Model\IngressHttpRule;
use Kubernetes\Client\Model\IngressRule;
use Ramsey\Uuid\UuidInterface;

class GenerateWhileCreatingDeploymentRequest implements DeploymentRequestFactory
{
    /**
     * @var DeploymentRequestFactory
     */
    private $decoratedFactory;

    /**
     * @var SslCertificateManager
     */
    private $sslCertificateManager;

    /**
     * @param DeploymentRequestFactory $decoratedFactory
     * @param SslCertificateManager $sslCertificateManager
     */
    public function __construct(DeploymentRequestFactory $decoratedFactory, SslCertificateManager $sslCertificateManager)
    {
        $this->decoratedFactory = $decoratedFactory;
        $this->sslCertificateManager = $sslCertificateManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $deploymentRequest = $this->decoratedFactory->create($tide, $taskDetails, $configuration);

        return new DeploymentRequest(
            $deploymentRequest->getTarget(),
            new DeploymentRequest\Specification(array_map(function (Component $component) use ($tide) {
                $component->setEndpoints(array_map(function(Component\Endpoint $endpoint) use ($tide) {
                    $endpoint->setSslCertificates(array_map(function(Component\Endpoint\SslCertificate $certificate) use ($tide, $endpoint) {
                        if ($certificate->getKey() == 'automatic' && $certificate->getCert() == 'automatic') {
                            return $this->generateCertificateForEndpoint($tide, $endpoint);
                        }

                        return $certificate;
                    }, $endpoint->getSslCertificates()));

                    return $endpoint;
                }, $component->getEndpoints()));

                return $component;
            }, $deploymentRequest->getSpecification()->getComponents())),
            $deploymentRequest->getNotification(),
            $deploymentRequest->getCredentialsBucket()
        );
    }

    private function generateCertificateForEndpoint(Tide $tide, Component\Endpoint $endpoint)
    {
        if (null === ($ingress = $endpoint->getIngress())) {
            throw new DeploymentRequestException('Can only generate SSL certificates with ingresses');
        } else if (0 === count($rules = $ingress->getRules())) {
            throw new DeploymentRequestException('Can only generate SSL certificates for ingresses with an hostname');
        }

        $hosts = array_unique(array_map(function (IngressRule $rule) {
            return $rule->getHost();
        }, $rules));

        if (count($hosts) != 1) {
            throw new DeploymentRequestException(sprintf('Cannot generate SSL certificate, found %d hostname(s) while expecting 1', count($hosts)));
        }

        return $this->generateCertificateForHostname($tide->getFlowUuid(), $hosts[0]);
    }

    private function generateCertificateForHostname(UuidInterface $flowUuid, string $hostname)
    {
        try {
            $certificate = $this->sslCertificateManager->findOrCreate($flowUuid, $hostname);
        } catch (SslCertificateException $e) {
            throw new DeploymentRequestException(sprintf('Can\'t generate the SSL certificate: %s', $e->getMessage()), $e->getCode(), $e);
        }

        return new Component\Endpoint\SslCertificate(
            $certificate->getName(),
            $certificate->getCert(),
            $certificate->getKey()
        );
    }
}
