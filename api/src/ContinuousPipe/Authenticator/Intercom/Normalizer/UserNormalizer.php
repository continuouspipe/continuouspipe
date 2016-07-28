<?php

namespace ContinuousPipe\Authenticator\Intercom\Normalizer;

use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;

class UserNormalizer
{
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @param TeamMembershipRepository $teamMembershipRepository
     */
    public function __construct(TeamMembershipRepository $teamMembershipRepository)
    {
        $this->teamMembershipRepository = $teamMembershipRepository;
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
            }),
        ];
    }
}
