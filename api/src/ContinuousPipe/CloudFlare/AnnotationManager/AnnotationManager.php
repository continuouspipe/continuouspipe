<?php

namespace ContinuousPipe\CloudFlare\AnnotationManager;

use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Exception\Exception;
use Kubernetes\Client\Model\KubernetesObject;

interface AnnotationManager
{
    /**
     * @param DeploymentContext $context
     * @param KubernetesObject $object
     * @param string $annotationName
     *
     * @throws Exception
     *
     * @return string|null
     */
    public function readAnnotation(DeploymentContext $context, KubernetesObject $object, string $annotationName);

    /**
     * @param DeploymentContext $context
     * @param KubernetesObject $object
     * @param string $annotationName
     * @param string $annotationValue
     *
     * @throws Exception
     */
    public function writeAnnotation(DeploymentContext $context, KubernetesObject $object, string $annotationName, string $annotationValue);

    /**
     * @param KubernetesObject $object
     *
     * @return bool
     */
    public function supports(KubernetesObject $object);
}
