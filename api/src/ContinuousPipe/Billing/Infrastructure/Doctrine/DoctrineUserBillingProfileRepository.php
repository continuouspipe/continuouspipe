<?php

namespace ContinuousPipe\Billing\Infrastructure\Doctrine;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\User\User;
use Doctrine\ORM\EntityManager;

class DoctrineUserBillingProfileRepository implements UserBillingProfileRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user): UserBillingProfile
    {
        if (null === ($billingProfile = $this->getRepository()->findOneBy(['user' => $user]))) {
            throw new UserBillingProfileNotFound(sprintf('No billing profile found for user "%s"', $user->getUsername()));
        }

        return $billingProfile;
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

    private function getRepository()
    {
        return $this->entityManager->getRepository(UserBillingProfile::class);
    }
}
