<?php

namespace ContinuousPipe\CloudFlare\AnnotationManager;

use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Exception\Exception;
use Kubernetes\Client\Model\KubernetesObject;

class ChainAnnotationManager implements AnnotationManager
{
    /**
     * @var array|AnnotationManager[]
     */
    private $annotationManagers;

    /**
     * @param AnnotationManager[] $annotationManagers
     */
    public function __construct(array $annotationManagers)
    {
        $this->annotationManagers = $annotationManagers;
    }

    /**
     * {@inheritdoc}
     */
    public function readAnnotation(DeploymentContext $context, KubernetesObject $object, string $annotationName)
    {
        foreach ($this->annotationManagers as $manager) {
            if ($manager->supports($object)) {
                return $manager->readAnnotation($context, $object, $annotationName);
            }
        }

        throw new Exception('Can\'t read the annotation from the object as no manager supports it.');
    }

    /**
     * {@inheritdoc}
     */
    public function writeAnnotation(DeploymentContext $context, KubernetesObject $object, string $annotationName, string $annotationValue)
    {
        foreach ($this->annotationManagers as $manager) {
            if ($manager->supports($object)) {
                return $manager->writeAnnotation($context, $object, $annotationName, $annotationValue);
            }
        }

        throw new Exception('Can\'t write the annotation from the object as no manager supports it.');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        foreach ($this->annotationManagers as $manager) {
            if ($manager->supports($object)) {
                return true;
            }
        }

        return false;
    }
}
