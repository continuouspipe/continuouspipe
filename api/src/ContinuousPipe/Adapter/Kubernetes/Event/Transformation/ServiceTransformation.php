<?php

namespace ContinuousPipe\Adapter\Kubernetes\Event\Transformation;

use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\Service;
use Symfony\Component\EventDispatcher\Event;

class ServiceTransformation extends Event
{
    const POST_SERVICE_TRANSFORMATION = 'transformer.service.post';

    /**
     * @var Component
     */
    private $component;

    /**
     * @var Service
     */
    private $service;

    /**
     * @param Component $component
     * @param Service   $service
     */
    public function __construct(Component $component, Service $service)
    {
        $this->component = $component;
        $this->service = $service;
    }

    /**
     * @return Component
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param Service $service
     */
    public function setService(Service $service)
    {
        $this->service = $service;
    }
}
