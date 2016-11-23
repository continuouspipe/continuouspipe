<?php

namespace ContinuousPipe\Authenticator\Invitation;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Security\User\UserRepository;

class InvitationToTeamMembershipTransformer
{
    /**
     * @var SecurityUserRepository
     */
    private $userRepository;
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @param SecurityUserRepository   $userRepository
     * @param TeamRepository           $teamRepository
     * @param TeamMembershipRepository $teamMembershipRepository
     */
    public function __construct(SecurityUserRepository $userRepository, TeamRepository $teamRepository, TeamMembershipRepository $teamMembershipRepository)
    {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
    }

    /**
     * @param UserInvitation $invitation
     *
     * @throws InvitationException
     *
     * @return TeamMembership
     */
    public function transformInvitation(UserInvitation $invitation, User $user)
    {
        try {
            $team = $this->teamRepository->find($invitation->getTeamSlug());
        } catch (TeamNotFound $e) {
            throw new InvitationException('Team is not found', $e->getCode(), $e);
        }

        $membership = new TeamMembership($team, $user, $invitation->getPermissions());

        $this->teamMembershipRepository->save($membership);

        return $membership;
    }
}
