<?php

namespace ContinuousPipe\Adapter\Kubernetes\Service;

use Kubernetes\Client\Model\Service as ModelService;

interface Service
{
    /**
     * @return ModelService
     */
    public function getService();
}