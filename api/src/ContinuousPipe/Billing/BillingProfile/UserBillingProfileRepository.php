<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Security\User\User;

interface UserBillingProfileRepository
{
    /**
     * Find the billing profile of the user.
     *
     * @param User $user
     *
     * @throws UserBillingProfileNotFound
     *
     * @return UserBillingProfile
     */
    public function findByUser(User $user) : UserBillingProfile;

    /**
     * @param UserBillingProfile $billingProfile
     */
    public function save(UserBillingProfile $billingProfile);
}
