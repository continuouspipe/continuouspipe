<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Security\User\User;

class InMemoryBillingProfileRepository implements UserBillingProfileRepository
{
    /**
     * @var UserBillingProfile[]
     */
    private $profiles = [];

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user): UserBillingProfile
    {
        foreach ($this->profiles as $profile) {
            if ($profile->getUser()->getUsername() == $user->getUsername()) {
                return $profile;
            }
        }

        throw new UserBillingProfileNotFound(sprintf(
            'No billing profile found for user %s',
            $user->getUsername()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserBillingProfile $billingProfile)
    {
        $this->profiles[] = $billingProfile;
    }
}
