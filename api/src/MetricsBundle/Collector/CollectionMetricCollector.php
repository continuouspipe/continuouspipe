<?php

namespace MetricsBundle\Collector;

class CollectionMetricCollector implements MetricCollector
{
    /**
     * @var array|MetricCollector[]
     */
    private $collection;

    /**
     * @param MetricCollector[] $collection
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return array_map(function (MetricCollector $collector) {
            return $collector->collect();
        }, $this->collection);
    }
}
