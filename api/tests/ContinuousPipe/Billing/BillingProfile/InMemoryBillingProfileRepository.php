<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class InMemoryBillingProfileRepository implements UserBillingProfileRepository
{
    /**
     * @var UserBillingProfile[]
     */
    private $profiles = [];

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid): UserBillingProfile
    {
        if (!array_key_exists($uuid->toString(), $this->profiles)) {
            throw new UserBillingProfileNotFound(sprintf(
                'No billing profile found with identifier %s',
                $uuid
            ));
        }

        return $this->profiles[$uuid->toString()];
    }

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
        $this->profiles[(string) $billingProfile->getUuid()] = $billingProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team): UserBillingProfile
    {
        foreach ($this->profiles as $profile) {
            if ($profile->getTeams()->exists(function(int $position, Team $usedTeam) use ($team) {
                return $usedTeam->getSlug() == $team->getSlug();
            })) {
                return $profile;
            }
        }

        throw new UserBillingProfileNotFound(sprintf(
            'No billing profile found for team %s',
            $team->getSlug()
        ));
    }
}
