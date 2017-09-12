<?php

namespace ContinuousPipe\River\Managed\Resources\Discrepancies;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Managed\Resources\Calculation\Interval;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCalculator;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use ContinuousPipe\River\Managed\Resources\ResourceUsage;
use ContinuousPipe\River\Managed\Resources\UsageProjection\FlowUsageProjector;
use Ramsey\Uuid\Uuid;

class RepairResourcesDiscrepanciesHandler
{
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;
    /**
     * @var ResourceUsageHistoryRepository
     */
    private $resourceUsageHistoryRepository;

    public function __construct(
        FlatFlowRepository $flatFlowRepository,
        ResourceUsageHistoryRepository $resourceUsageHistoryRepository
    ) {
        $this->flatFlowRepository = $flatFlowRepository;
        $this->resourceUsageHistoryRepository = $resourceUsageHistoryRepository;
    }

    public function handle(RepairResourcesDiscrepancies $command)
    {
        $flow = $this->flatFlowRepository->find($command->getFlowUuid());

        $interval = new Interval($command->getLeftInterval(), $command->getRightInterval());
        $history = $this->resourceUsageHistoryRepository->findByFlowAndDateInterval($flow->getUuid(), $interval);

        $usageCalculator = new UsageSnapshotCalculator();

        (new Interval($command->getLeftInterval(), $command->getRightInterval()))->forEachInterval(new \DateInterval('PT12H'), function (\DateTime $left, \DateTime $right) use ($history, $command, $usageCalculator) {
            $historyWithinInterval = $this->historyWithinInterval($history, $left, $right);
            foreach ($historyWithinInterval as $entry) {
                $usageCalculator->updateWith($entry);
            }

            $usagePerEnvironment = $usageCalculator->getUsagePerEnvironment();

            foreach ($usagePerEnvironment as $environmentName => $usage) {
                if ($usage->isZero()) {
                    continue;
                }

                if (!$this->historyHasEnvironment($historyWithinInterval, $environmentName)) {
                    $missingZeroEntry = new ResourceUsageHistory(
                        Uuid::uuid4(),
                        $command->getFlowUuid(),
                        $environmentName,
                        ResourceUsage::zero(),
                        $left
                    );

                    // Save the missing entry
                    $this->resourceUsageHistoryRepository->save($missingZeroEntry);

                    // Apply the missing entry to the calculator
                    $usageCalculator->updateWith($missingZeroEntry);
                }
            }
        });
    }

    /**
     * @param ResourceUsageHistory[] $history
     * @param \DateTime $left
     * @param \DateTime $right
     *
     * @return ResourceUsageHistory[]
     */
    private function historyWithinInterval(array $history, \DateTime $left, \DateTime $right) : array
    {
        $entriesWithinRange = [];

        foreach ($history as $entry) {
            if ($entry->getDateTime() >= $left && $entry->getDateTime() <= $right) {
                $entriesWithinRange[] = $entry;
            }
        }

        return $entriesWithinRange;
    }

    /**
     * @param ResourceUsageHistory[] $history
     * @param string $environmentName
     *
     * @return bool
     */
    private function historyHasEnvironment(array $history, string $environmentName) : bool
    {
        foreach ($history as $entry) {
            if ($entry->getEnvironmentIdentifier() == $environmentName) {
                return true;
            }
        }

        return false;
    }
}
