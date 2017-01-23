<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

interface UserBillingProfileRepository
{
    /**
     * @param UuidInterface $uuid
     *
     * @throws UserBillingProfileNotFound
     *
     * @return UserBillingProfile
     */
    public function find(UuidInterface $uuid) : UserBillingProfile;

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
     * Find the billing profile for the given team.
     *
     * @param Team $team
     *
     * @throws UserBillingProfileNotFound
     *
     * @return UserBillingProfile
     */
    public function findByTeam(Team $team) : UserBillingProfile;

    /**
     * @param UserBillingProfile $billingProfile
     */
    public function save(UserBillingProfile $billingProfile);
}
