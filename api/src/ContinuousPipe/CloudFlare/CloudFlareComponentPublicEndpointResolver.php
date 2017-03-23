<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Adapter\Kubernetes\ReverseTransformer\ComponentPublicEndpointResolver;
use Kubernetes\Client\Model\KubernetesObject;

class CloudFlareComponentPublicEndpointResolver implements ComponentPublicEndpointResolver
{
    public function resolve(KubernetesObject $serviceOrIngress)
    {
        $cloudFlareAnnotation = $serviceOrIngress->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.cloudflare.zone');
        if (null !== $cloudFlareAnnotation) {
            $cloudFlareMetadata = \GuzzleHttp\json_decode($cloudFlareAnnotation->getValue(), true);
            return $cloudFlareMetadata['record_name'];
        }
    }
}
