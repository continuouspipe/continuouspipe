<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\SslCertificate\SslCertificateException;
use ContinuousPipe\River\SslCertificate\SslCertificateManager;
use ContinuousPipe\River\Task\TaskContext;

class CertificateGenerationEnhancer implements EndpointConfigurationEnhancer
{
    /**
     * @var SslCertificateManager
     */
    private $sslCertificateManager;

    /**
     * @param SslCertificateManager $sslCertificateManager
     */
    public function __construct(SslCertificateManager $sslCertificateManager)
    {
        $this->sslCertificateManager = $sslCertificateManager;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(array $endpointConfiguration, TaskContext $context)
    {
        if (!isset($endpointConfiguration['ssl_certificates'])) {
            return $endpointConfiguration;
        }

        $endpointConfiguration['ssl_certificates'] = array_map(function ($certificate) use ($context, $endpointConfiguration) {
            if ($certificate['key'] == 'automatic' && $certificate['cert'] == 'automatic') {
                return $this->generateCertificateForEndpoint($context, $endpointConfiguration);
            }

            return $certificate;
        }, $endpointConfiguration['ssl_certificates']);

        return $endpointConfiguration;
    }

    private function generateCertificateForEndpoint(TaskContext $context, array $endpointConfiguration)
    {
        if (!isset($endpointConfiguration['ingress']['rules'])) {
            throw new TideGenerationException('Can only generate SSL certificates with ingresses');
        }

        $hosts = array_unique(array_map(function (array $rule) {
            return $rule['host'];
        }, $endpointConfiguration['ingress']['rules']));

        if (count($hosts) != 1) {
            throw new TideGenerationException(sprintf('Found %d hostname(s), expected 1', count($hosts)));
        }

        return $this->generateCertificateForHostname($context, $hosts[0]);
    }

    private function generateCertificateForHostname(TaskContext $context, string $hostname)
    {
        try {
            $certificate = $this->sslCertificateManager->findOrCreate($context->getFlowUuid(), $hostname);
        } catch (SslCertificateException $e) {
            throw new TideGenerationException(sprintf('Can\'t generate the SSL certificate: %s', $e->getMessage()), $e->getCode(), $e);
        }

        return [
            'name' => $certificate->getName(),
            'cert' => $certificate->getCert(),
            'key' => $certificate->getKey(),
        ];
    }
}
