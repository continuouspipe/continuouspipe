<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Adapter\Kubernetes\Inspector\ReverseTransformer\ComponentPublicEndpointResolver;
use Kubernetes\Client\Model\KubernetesObject;
use Psr\Log\LoggerInterface;

class CloudFlareComponentPublicEndpointResolver implements ComponentPublicEndpointResolver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(KubernetesObject $serviceOrIngress) : array
    {
        $publicEndpoints = [];

        // TODO: Remove this deprecated usage of `com.continuouspipe.io.cloudflare.zone`
        $cloudFlareAnnotation = $serviceOrIngress->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.cloudflare.zone');
        if (null !== $cloudFlareAnnotation) {
            try {
                $cloudFlareMetadata = \GuzzleHttp\json_decode($cloudFlareAnnotation->getValue(), true);
                $publicEndpoints[] = $cloudFlareMetadata['record_name'];
            } catch (\InvalidArgumentException $exception) {
                $this->logger->warning('Cannot gather CloudFlare data from annotation', ['service_or_ingress' => $serviceOrIngress, 'exception' => $exception]);
            }
        }

        $cloudFlareAnnotation = $serviceOrIngress->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.cloudflare.records');
        if (null !== $cloudFlareAnnotation) {
            try {
                $cloudFlareMetadata = \GuzzleHttp\json_decode($cloudFlareAnnotation->getValue(), true);

                foreach ($cloudFlareMetadata as $record) {
                    $publicEndpoints[] = $record['record_name'];
                }
            } catch (\InvalidArgumentException $exception) {
                $this->logger->warning('Cannot gather CloudFlare data from annotation', ['service_or_ingress' => $serviceOrIngress, 'exception' => $exception]);
            }
        }

        return $publicEndpoints;
    }
}
