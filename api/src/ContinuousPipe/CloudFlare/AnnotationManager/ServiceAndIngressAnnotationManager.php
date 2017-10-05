<?php

namespace ContinuousPipe\CloudFlare\AnnotationManager;

use ContinuousPipe\Pipe\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Exception\Exception;
use Kubernetes\Client\Model\Annotation;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Repository\IngressRepository;
use Kubernetes\Client\Repository\ServiceRepository;

class ServiceAndIngressAnnotationManager implements AnnotationManager
{
    /**
     * @var DeploymentClientFactory
     */
    private $deploymentClientFactory;

    /**
     * @param DeploymentClientFactory $deploymentClientFactory
     */
    public function __construct(DeploymentClientFactory $deploymentClientFactory)
    {
        $this->deploymentClientFactory = $deploymentClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function readAnnotation(DeploymentContext $context, KubernetesObject $object, string $annotationName)
    {
        $object = $this->repository($context, $object)->findOneByName($object->getMetadata()->getName());

        if (null !== ($annotation = $object->getMetadata()->getAnnotationList()->get($annotationName))) {
            return $annotation->getValue();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function writeAnnotation(DeploymentContext $context, KubernetesObject $object, string $annotationName, string $annotationValue)
    {
        $repository = $this->repository($context, $object);
        $object = $repository->findOneByName($object->getMetadata()->getName());
        $object->getMetadata()->getAnnotationList()->add(new Annotation($annotationName, $annotationValue));

        $repository->annotate($object->getMetadata()->getName(), $object->getMetadata()->getAnnotationList());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Service || $object instanceof Ingress;
    }

    /**
     * @param DeploymentContext $context
     * @param KubernetesObject $object
     *
     * @return IngressRepository|ServiceRepository
     */
    private function repository(DeploymentContext $context, KubernetesObject $object)
    {
        $deploymentClient = $this->deploymentClientFactory->get($context);

        if ($object instanceof Service) {
            return $deploymentClient->getServiceRepository();
        } elseif ($object instanceof Ingress) {
            return $deploymentClient->getIngressRepository();
        }

        throw new \InvalidArgumentException('This object is not supported');
    }
}
