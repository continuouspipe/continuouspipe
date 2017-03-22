<?php

namespace ContinuousPipe\Tolerance\Metrics\Publisher;

use Tolerance\Metrics\Metric;
use Tolerance\Metrics\Publisher\MetricPublisher;

class TracedPublisher implements MetricPublisher
{
    /**
     * @var MetricPublisher
     */
    private $publisher;

    /**
     * @var Metric[]
     */
    private $publishedMetrics = [];

    /**
     * @param MetricPublisher $publisher
     */
    public function __construct(MetricPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(array $metrics)
    {
        $this->publisher->publish($metrics);

        $this->publishedMetrics = array_merge($this->publishedMetrics, $metrics);
    }

    /**
     * @return Metric[]
     */
    public function getPublishedMetrics()
    {
        return $this->publishedMetrics;
    }
}
