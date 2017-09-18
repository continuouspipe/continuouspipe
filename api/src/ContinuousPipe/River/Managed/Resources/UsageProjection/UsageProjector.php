<?php

namespace ContinuousPipe\River\Managed\Resources\UsageProjection;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

class UsageProjector
{
    /**
     * @var FlowUsageProjector
     */
    private $flowUsageProjector;

    public function __construct(FlowUsageProjector $flowUsageProjector)
    {
        $this->flowUsageProjector = $flowUsageProjector;
    }

    public function forFlows(array $flows, \DateTime $left, \DateTime $right, \DateInterval $interval)
    {
        $usages = array_reduce($flows, function (array $carry, FlatFlow $flow) use ($left, $right, $interval) {
            $flowUsages = $this->mergeUsages(array_merge(
                $this->flowUsageProjector->getTideUsage($flow, $left, $right, $interval),
                $this->flowUsageProjector->getResourcesUsage($flow, $left, $right, $interval)
            ));

            return array_merge(
                $carry,
                array_map(function (array $usage) use ($flow) {
                    $usage['flow'] = [
                        'uuid' => $flow->getUuid()->toString(),
                        'name' => $flow->getRepository()->getAddress(),
                    ];

                    $usage['team'] = [
                        'slug' => $flow->getTeam()->getSlug(),
                        'name' => $flow->getTeam()->getName(),
                    ];

                    return $usage;
                }, $flowUsages)
            );
        }, []);

        return $this->aggregateUsageEntriesByDateTime($usages);
    }

    /**
     * @param array $usages
     *
     * @return array
     */
    private function mergeUsages(array $usages) : array
    {
        return array_map(function (array $usage) {
            $usage['usage'] = array_reduce($usage['entries'], function (array $carry, array $entry) {
                return array_merge($carry, $entry['usage']);
            }, []);

            unset($usage['entries']);

            return $usage;
        }, $this->aggregateUsageEntriesByDateTime($usages));
    }

    private function aggregateUsageEntriesByDateTime(array $usages)
    {
        $aggregated = [];

        foreach ($usages as $usage) {
            $key = $usage['datetime']['left']->getTimestamp().'-'.$usage['datetime']['right']->getTimestamp();

            if (!array_key_exists($key, $aggregated)) {
                $aggregated[$key] = [
                    'datetime' => $usage['datetime'],
                    'entries' => [],
                ];
            }

            unset($usage['datetime']);
            $aggregated[$key]['entries'][] = $usage;
        }

        return array_values($aggregated);
    }
}
