<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Adapter\Kubernetes\ReverseTransformer\ComponentPublicEndpointResolver;
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
        $cloudFlareAnnotation = $serviceOrIngress->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.cloudflare.zone');
        if (null !== $cloudFlareAnnotation) {
            try {
                $cloudFlareMetadata = \GuzzleHttp\json_decode($cloudFlareAnnotation->getValue(), true);
                $publicEndpoints[] = $cloudFlareMetadata['record_name'];
            } catch (\InvalidArgumentException $exception) {
                $this->logger->warning($exception->getMessage(), ['service_or_ingress' => $serviceOrIngress]);
            }
        }

        return $publicEndpoints;
    }
}
