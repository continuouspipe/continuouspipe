<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class InMemoryBillingProfileRepository implements UserBillingProfileRepository
{
    /**
     * @var UserBillingProfile[]
     */
    private $profiles = [];

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

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

        $profile = $this->profiles[$uuid->toString()];
        $profile->setTeams($profile->getTeams()->map(function(Team $team) {
            return $this->teamRepository->find($team->getSlug());
        }));

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user): array
    {
        $profiles = [];
        foreach ($this->profiles as $profile) {
            if ($profile->isAdmin($user)) {
                $profiles[] = $profile;
            }
        }

        return $profiles;
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
            if ($profile->getTeams()->filter(function(Team $teamProfile) use ($team) {
                return $teamProfile->getSlug() == $team->getSlug();
            })->count() > 0) {
                return $profile;
            }
        }

        throw new UserBillingProfileNotFound('No found for this team');
    }

    /**
     * {@inheritdoc}
     */
    public function link(Team $team, UserBillingProfile $billingProfile)
    {
        $this->find($billingProfile->getUuid())->getTeams()->add($team);
    }

    /**
     * {@inheritdoc}
     */
    public function unlink(Team $team, UserBillingProfile $billingProfile)
    {
        $this->find($billingProfile->getUuid())->getTeams()->removeElement($team);
    }

    /**
     * {@inheritdoc}
     */
    public function findRelations(UuidInterface $billingProfileUuid)
    {
        return $this->find($billingProfileUuid)->getTeams();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UserBillingProfile $billingProfile)
    {
        foreach ($this->profiles as $index => $profile) {
            if ($profile->getUuid()->equals($billingProfile->getUuid())) {
                if ($profile->getTeams()->count() > 0) {
                    throw new UserBillingProfileException('The billing profile is linked with some resources that needs to be deleted before', 400);
                }

                unset($this->profiles[$index]);
            }
        }
    }
}
