<?php


namespace ContinuousPipe\River\Managed\Resources\Calculation;

use ContinuousPipe\River\Managed\Resources\ResourceUsage;

class UsageSnapshotCollection
{
    /**
     * @var UsageSnapshot[]
     */
    private $snapshots = [];

    /**
     * @param \DateTimeInterface $dateTime
     * @param ResourceUsage $usage
     */
    public function add(\DateTimeInterface $dateTime, ResourceUsage $usage)
    {
        $this->snapshots[] = new UsageSnapshot($dateTime, $usage);

        usort($this->snapshots, function (UsageSnapshot $left, UsageSnapshot $right) {
            return $left->getDateTime() > $right->getDateTime() ? 1 : -1;
        });
    }

    /**
     * @param \DateTimeInterface $left
     * @param \DateTimeInterface $right
     *
     * @return ResourceUsage|null
     */
    public function highestUsageInInterval(\DateTimeInterface $left, \DateTimeInterface $right)
    {
        $snapshots = $this->snapshotsInInterval($left, $right);
        if (count($snapshots) == 0) {
            return null;
        }

        $usage = ResourceUsage::zero();

        foreach ($snapshots as $snapshot) {
            $usage = $usage->max($snapshot->getUsage());
        }

        return $usage;
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return ResourceUsage|null
     */
    public function lastBefore(\DateTimeInterface $dateTime)
    {
        for ($i = count($this->snapshots) - 1; $i >= 0; $i--) {
            $snapshot = $this->snapshots[$i];

            if ($snapshot->getDateTime() < $dateTime) {
                return $snapshot->getUsage();
            }
        }

        return null;
    }

    /**
     * @return UsageSnapshot[]
     */
    public function all()
    {
        return $this->snapshots;
    }

    /**
     * @param \DateTimeInterface $left
     * @param \DateTimeInterface $right
     *
     * @return UsageSnapshot[]
     */
    private function snapshotsInInterval(\DateTimeInterface $left, \DateTimeInterface $right)
    {
        return array_values(array_filter($this->snapshots, function (UsageSnapshot $snapshot) use ($left, $right) {
            return $snapshot->getDateTime() >= $left && $snapshot->getDateTime() <= $right;
        }));
    }
}
