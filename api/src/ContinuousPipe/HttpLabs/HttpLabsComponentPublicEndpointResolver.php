<?php

namespace ContinuousPipe\HttpLabs;

use ContinuousPipe\Adapter\Kubernetes\Inspector\ReverseTransformer\ComponentPublicEndpointResolver;
use Kubernetes\Client\Model\KubernetesObject;
use Psr\Log\LoggerInterface;

class HttpLabsComponentPublicEndpointResolver implements ComponentPublicEndpointResolver
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

        $httpLabsAnnotation = $serviceOrIngress->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.httplabs.stack');
        if (null !== $httpLabsAnnotation) {
            try {
                $httpLabsMetadata = \GuzzleHttp\json_decode($httpLabsAnnotation->getValue(), true);

                if (isset($httpLabsMetadata['stack_address'])) {
                    $publicEndpoints[] = $httpLabsMetadata['stack_address'];
                }
            } catch (\InvalidArgumentException $exception) {
                $this->logger->warning('Cannot gather HttpLabs data from annotation', ['service_or_ingress' => $serviceOrIngress, 'exception' => $exception]);
            }
        }

        return $publicEndpoints;
    }
}
