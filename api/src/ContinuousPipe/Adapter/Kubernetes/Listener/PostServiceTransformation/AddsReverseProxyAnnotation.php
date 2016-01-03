<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\PostServiceTransformation;

use ContinuousPipe\Adapter\Kubernetes\Event\Transformation\ServiceTransformation;
use ContinuousPipe\Model\Extension\ReverseProxy\ReverseProxyExtension;
use Kubernetes\Client\Model\Annotation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddsReverseProxyAnnotation implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ServiceTransformation::POST_SERVICE_TRANSFORMATION => 'postTransformation',
        ];
    }

    /**
     * @param ServiceTransformation $event
     */
    public function postTransformation(ServiceTransformation $event)
    {
        $component = $event->getComponent();
        $service = $event->getService();

        /** @var $extension ReverseProxyExtension */
        if (null === ($extension = $component->getExtension(ReverseProxyExtension::NAME))) {
            return;
        }

        $annotationValue = json_encode([
            'hosts' => array_map(function ($domainName) {
                return [
                    'host' => $domainName,
                    'port' => '80',
                ];
            }, $extension->getDomainNames()),
        ]);

        $service->getMetadata()->getAnnotationList()->add(new Annotation(
            'kubernetesReverseProxy',
            $annotationValue
        ));
    }
}
