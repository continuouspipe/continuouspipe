<?php

namespace ContinuousPipe\Authenticator\Intercom\Normalizer;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Billing\BillingProfile\Trial\TrialResolver;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;

class UserNormalizer
{
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingPorfileRepository;

    /**
     * @param TeamMembershipRepository     $teamMembershipRepository
     * @param UserBillingProfileRepository $userBillingProfileRepository
     */
    public function __construct(TeamMembershipRepository $teamMembershipRepository, UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->userBillingPorfileRepository = $userBillingProfileRepository;
    }

    /**
     * Normalize the user representation.
     *
     * @param User $user
     *
     * @return array
     */
    public function normalize(User $user)
    {
        return [
            'user_id' => $user->getUsername(),
            'email' => $user->getEmail(),
            'name' => $user->getUsername(),
            'new_session' => true,
            'companies' => $this->teamMembershipRepository->findByUser($user)->map(function (TeamMembership $teamMembership) {
                return [
                    'company_id' => $teamMembership->getTeam()->getSlug(),
                    'name' => $teamMembership->getTeam()->getName(),
                ];
            })->toArray()
        ];
    }
}
