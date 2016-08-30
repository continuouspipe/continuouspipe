<?php

namespace ContinuousPipe\River\Filter\FilterHash;

use ContinuousPipe\River\Filter\FilterEvaluator;
use ContinuousPipe\River\Tide;

class FilterHashEvaluator
{
    /**
     * @var FilterEvaluator
     */
    private $filterEvaluator;

    /**
     * @param FilterEvaluator $filterEvaluator
     */
    public function __construct(FilterEvaluator $filterEvaluator)
    {
        $this->filterEvaluator = $filterEvaluator;
    }

    /**
     * @param Tide $tide
     *
     * @return FilterHash
     */
    public function evaluates(Tide $tide)
    {
        $configuration = $tide->getContext()->getConfiguration();
        $filters = $this->extractFilters($configuration);
        $evaluatedFilters = array_map(function (array $filter) use ($tide) {
            return $this->filterEvaluator->evaluates($tide, $filter);
        }, $filters);

        return new FilterHash($tide->getUuid(), md5(json_encode($evaluatedFilters)));
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    private function extractFilters(array $configuration)
    {
        $filters = [];

        if (array_key_exists('filter', $configuration)) {
            $filters[] = $configuration['filter'];
        }

        foreach ($configuration['tasks'] as $taskName => $task) {
            if (array_key_exists('filter', $task)) {
                $filters[$taskName] = $task['filter'];
            }
        }

        return $filters;
    }
}
