<?php

namespace ContinuousPipe\Billing\Infrastructure\Doctrine;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;

class DoctrineUserBillingProfileRepository implements UserBillingProfileRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(EntityManager $entityManager, TeamRepository $teamRepository)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user): UserBillingProfile
    {
        if (null === ($billingProfile = $this->getUserBillingProfileRepository()->findOneBy(['user' => $user]))) {
            throw new UserBillingProfileNotFound(sprintf('No billing profile found for user "%s"', $user->getUsername()));
        }

        return $billingProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByUser(User $user): array
    {
        return $this->getUserBillingProfileRepository()->findBy(['user' => $user]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserBillingProfile $billingProfile)
    {
        $merged = $this->entityManager->merge($billingProfile);

        $this->entityManager->persist($merged);
        $this->entityManager->flush($merged);
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid): UserBillingProfile
    {
        if (null === ($billingProfile = $this->getUserBillingProfileRepository()->find($uuid->toString()))) {
            throw new UserBillingProfileNotFound(sprintf(
                'No billing profile found with identifier %s',
                $uuid
            ));
        }

        return $billingProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function link(Team $team, UserBillingProfile $billingProfile)
    {
        $team = $this->entityManager->merge($team);
        $billingProfile = $this->entityManager->merge($billingProfile);

        $relation = new UserBillingProfileTeamRelation($team, $billingProfile);

        $this->entityManager->persist($relation);
        $this->entityManager->flush($relation);
    }

    /**
     * {@inheritdoc}
     */
    public function unlink(Team $team, UserBillingProfile $billingProfile)
    {
        $relation = $this->getUserBillingProfileTeamRelationRepository()->findOneBy([
            'team' => $team,
            'userBillingProfile' => $billingProfile,
        ]);

        if (null !== $relation) {
            $this->entityManager->remove($relation);
            $this->entityManager->flush($relation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team): UserBillingProfile
    {
        $query = $this->getUserBillingProfileTeamRelationRepository()
            ->createQueryBuilder('relation')
            ->addSelect('billingProfile')
            ->join('relation.userBillingProfile', 'billingProfile')
            ->where('relation.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
        ;

        /** @var UserBillingProfileTeamRelation $billingProfileRelation */
        if (null === ($billingProfileRelation = $query->getOneOrNullResult())) {
            throw new UserBillingProfileNotFound(sprintf(
                'No billing profile found for team %s',
                $team->getSlug()
            ));
        }

        return $billingProfileRelation->getUserBillingProfile();
    }

    /**
     * {@inheritdoc}
     */
    public function findRelations(UuidInterface $billingProfileUuid)
    {
        $query = $this->getUserBillingProfileTeamRelationRepository()
            ->createQueryBuilder('relation')
            ->addSelect('team')
            ->join('relation.team', 'team')
            ->where('relation.userBillingProfile = :billingProfile')
            ->setParameter('billingProfile', $billingProfileUuid)
            ->getQuery()
        ;

        return array_map(function (UserBillingProfileTeamRelation $relation) {
            return $relation->getTeam();
        }, $query->getResult());
    }

    private function getUserBillingProfileTeamRelationRepository()
    {
        return $this->entityManager->getRepository(UserBillingProfileTeamRelation::class);
    }

    private function getUserBillingProfileRepository()
    {
        return $this->entityManager->getRepository(UserBillingProfile::class);
    }
}
