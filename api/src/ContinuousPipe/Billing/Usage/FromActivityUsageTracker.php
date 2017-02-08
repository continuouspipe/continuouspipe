<?php

namespace ContinuousPipe\Billing\Usage;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\UuidInterface;

class FromActivityUsageTracker implements UsageTracker
{
    /**
     * @var ActivityTracker
     */
    private $activityTracker;
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    public function __construct(ActivityTracker $activityTracker, UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->activityTracker = $activityTracker;
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsage(UuidInterface $billingProfileUuid, \DateTimeInterface $start, \DateTimeInterface $end): Usage
    {
        /** @var UserActivity[] $activities */
        $activities = array_reduce(
            $this->userBillingProfileRepository->findRelations($billingProfileUuid),
            function(array $activities, Team $team) use ($start, $end) {
                return array_merge($activities, $this->activityTracker->findBy($team, $start, $end));
            },
            []
        );

        $distinctUserNames = [];

        foreach ($activities as $activity) {
            $username = $activity->getUser()->getUsername();

            if (!in_array($username, $distinctUserNames)) {
                $distinctUserNames[] = $username;
            }
        }

        return new Usage(
            count($distinctUserNames)
        );
    }
}
