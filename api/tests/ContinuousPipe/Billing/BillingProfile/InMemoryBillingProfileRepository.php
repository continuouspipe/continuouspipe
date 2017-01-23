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
     * The key being the team slug.
     *
     * @var array<string,UserBillingProfile>
     */
    private $links = [];

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
        if (!array_key_exists($team->getSlug(), $this->links)) {
            throw new UserBillingProfileNotFound(sprintf(
                'No billing profile found for team %s',
                $team->getSlug()
            ));
        }

        return $this->links[$team->getSlug()];
    }

    /**
     * {@inheritdoc}
     */
    public function link(Team $team, UserBillingProfile $billingProfile)
    {
        $this->links[$team->getSlug()] = $billingProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function unlink(Team $team, UserBillingProfile $billingProfile)
    {
        if (!array_key_exists($team->getSlug(), $this->links)) {
            return;
        }

        unset($this->links[$team->getSlug()]);
    }

    /**
     * {@inheritdoc}
     */
    public function findRelations(UserBillingProfile $billingProfile)
    {
        $teamSlugs = [];

        foreach ($this->links as $teamSlug => $profile) {
            if ($profile->getUuid()->equals($billingProfile->getUuid())) {
                $teamSlugs[] = $teamSlug;
            }
        }

        return array_map(function(string $slug) {
            return $this->teamRepository->find($slug);
        }, $teamSlugs);
    }
}
