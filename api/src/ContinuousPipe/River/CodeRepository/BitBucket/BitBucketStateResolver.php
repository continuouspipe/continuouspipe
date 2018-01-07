<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\Tide\Status\Status;

class BitBucketStateResolver
{
    /**
     * @param Status $status
     *
     * @return string
     */
    public static function fromStatus(Status $status)
    {
        $state = $status->getState();

        if ($state == Status::STATE_FAILURE) {
            return BuildStatus::STATE_FAILED;
        } elseif ($state == Status::STATE_SUCCESS) {
            return BuildStatus::STATE_SUCCESSFUL;
        } elseif ($state == Status::STATE_PENDING) {
            return BuildStatus::STATE_STOPPED;
        }

        return BuildStatus::STATE_IN_PROGRESS;
    }
}
