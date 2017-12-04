<?php

namespace ContinuousPipe\Authenticator\Team;

use ContinuousPipe\Billing\BillingProfile\Request\UserBillingProfileCreationRequest;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileCreator;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamUsageLimits;
use Doctrine\Common\Collections\ArrayCollection;

class TeamUsageLimitsRepositoryFromBillingProfile implements TeamUsageLimitsRepository
{
    /**
     * @var UserBillingProfileRepository
     */
    private $billingProfileRepository;
    /**
     * @var UserBillingProfileCreator
     */
    private $billingProfileCreator;
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    public function __construct(
        UserBillingProfileRepository $billingProfileRepository,
        UserBillingProfileCreator $billingProfileCreator,
        TeamMembershipRepository $teamMembershipRepository
    ) {
        $this->billingProfileRepository = $billingProfileRepository;
        $this->billingProfileCreator = $billingProfileCreator;
        $this->teamMembershipRepository = $teamMembershipRepository;
    }

    public function findByTeam(Team $team) : TeamUsageLimits
    {
        $userBillingProfile = $this->billingProfileRepository->findByTeam($team);

        return new TeamUsageLimits(
            $userBillingProfile->getTidesPerHour()
        );
    }

    public function save(Team $team, TeamUsageLimits $limits)
    {
        try {
            $billingProfile = $this->billingProfileRepository->findByTeam($team);
        } catch (UserBillingProfileNotFound $e) {
            $billingProfile = $this->billingProfileCreator->create(
                new UserBillingProfileCreationRequest($team->getName() ?: $team->getSlug()),
                $this->teamMembershipRepository->findByTeam($team)->admins()->toArray()
            );

            // Add the team to the billing profile
            $billingProfile->setTeams(new ArrayCollection([$team]));
        }

        $billingProfile->setTidesPerHour($limits->getTidesPerHour());

        $this->billingProfileRepository->save($billingProfile);
    }
}
