<?php

namespace ContinuousPipe\Billing\Infrastructure\Doctrine;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Billing\Infrastructure\Doctrine\Entity\UserActivity as UserActivityEntity;
use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\Team\Team;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineActivityTracker implements ActivityTracker
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Track the user activity.
     *
     * @param UserActivity $userActivity
     */
    public function track(UserActivity $userActivity)
    {
        $entity = $this->entityManager->merge(new UserActivityEntity($userActivity));
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Find the user activity for this flow between start and end ranges.
     *
     * @param Team $team
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     *
     * @return UserActivity[]
     */
    public function findBy(Team $team, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->from(UserActivityEntity::class, 'ua')
            ->select('ua')
            ->where('ua.userActivity.teamSlug = :teamSlug')
            ->andWhere('ua.userActivity.dateTime >= :startTime')
            ->andWhere('ua.userActivity.dateTime <= :endTime')
            ->getQuery();


        $result = $query->execute(['teamSlug' => $team->getSlug(), 'startTime' => $start, 'endTime' => $end]);

        $activities = [];
        /** @var UserActivityEntity $activity */
        foreach ($result as $activity) {
            $activities = array_merge(
                $activities,
                array_fill(0, $activity->getEventCount(), $activity->getUserActivity())
            );
        }
        return $activities;
    }
}
