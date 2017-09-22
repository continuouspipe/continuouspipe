<?php

namespace ContinuousPipe\River\Managed\Resources\UsageProjection;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Managed\Resources\Calculation\Interval;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCalculator;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCollection;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use ContinuousPipe\River\Managed\Resources\ResourceUsage;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Doctrine\Common\Collections\ArrayCollection;

class FlowUsageProjector
{
    /**
     * @var ResourceUsageHistoryRepository
     */
    private $resourceUsageHistoryRepository;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    public function __construct(
        ResourceUsageHistoryRepository $resourceUsageHistoryRepository,
        TideRepository $tideRepository
    ) {
        $this->resourceUsageHistoryRepository = $resourceUsageHistoryRepository;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param FlatFlow $flow
     * @param \DateTime $left
     * @param \DateTime $right
     * @param \DateInterval $interval
     *
     * @return array
     */
    public function getResourcesUsage(FlatFlow $flow, \DateTime $left, \DateTime $right, \DateInterval $interval) : array
    {
        $history = $this->resourceUsageHistoryRepository->findByFlow($flow->getUuid());
        $usageCalculator = new UsageSnapshotCalculator();
        $snapshotsCollection = new UsageSnapshotCollection();

        // From the history to point-in-time snapshot
        foreach ($history as $entry) {
            $usageCalculator->updateWith($entry);

            $snapshotsCollection->add(
                $entry->getDateTime(),
                $usageCalculator->snapshot()
            );
        }

        return (new Interval($left, $right))->foreachInterval($interval, function (\DateTimeInterface $left, \DateTimeInterface $right) use ($snapshotsCollection) {
            $previousUsage = $snapshotsCollection->lastBefore($left) ?: ResourceUsage::zero();

            if (null === ($usageInInterval = $snapshotsCollection->highestUsageInInterval($left, $right))) {
                $usageInInterval = $previousUsage;
            }

            $resources = $usageInInterval->max($previousUsage)->getLimits();

            return [
                'datetime' => [
                    'left' => clone $left,
                    'right' => clone $right,
                ],
                'usage' => [
                    'cpu' => $resources->getCpu(),
                    'memory' => $resources->getMemory(),
                ],
            ];
        });
    }

    /**
     * @param FlatFlow $flow
     * @param \DateTime $left
     * @param \DateTime $right
     * @param \DateInterval $interval
     *
     * @return array
     */
    public function getTideUsage(FlatFlow $flow, \DateTime $left, \DateTime $right, \DateInterval $interval): array
    {
        $tideCollection = new ArrayCollection(
            $this->tideRepository->findByFlowBetween($flow->getUuid(), $left, $right)->toArray()
        );

        return (new Interval($left, $right))->foreachInterval($interval, function (\DateTimeInterface $left, \DateTimeInterface $right) use ($tideCollection) {
            return [
                'datetime' => [
                    'left' => clone $left,
                    'right' => clone $right,
                ],
                'usage' => [
                    'tides' => $tideCollection
                        ->filter(function (Tide $tide) use ($left, $right) {
                            return $tide->getCreationDate() >= $left && $tide->getCreationDate() <= $right;
                        })
                        ->count(),
                ],
            ];
        });
    }
}
