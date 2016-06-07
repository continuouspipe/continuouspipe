<?php

namespace MetricsBundle\Controller;

use MetricsBundle\Collector\MetricCollector;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="metrics.controller.metrics")
 */
class MetricsController
{
    /**
     * @var MetricCollector
     */
    private $collector;

    /**
     * @param MetricCollector $collector
     */
    public function __construct(MetricCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @Route("/metrics", methods={"GET"})
     * @View
     */
    public function listAction()
    {
        return $this->collector->collect();
    }
}
