<?php

namespace MetricsBundle\Collector;

interface MetricCollector
{
    /**
     * @return array
     */
    public function collect();
}
