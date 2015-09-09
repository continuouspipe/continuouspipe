<?php

namespace ContinuousPipe\Adapter\Kubernetes\Service;

use Kubernetes\Client\Model\Service as ModelService;

class CreatedService implements Service
{
    /**
     * @var ModelService
     */
    private $service;

    public function __construct(ModelService $service)
    {
        $this->service = $service;
    }

    /**
     * @return ModelService
     */
    public function getService()
    {
        return $this->service;
    }
}