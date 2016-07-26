<?php

namespace ContinuousPipe\Authenticator\Tests\Invitation;

use ContinuousPipe\Authenticator\Invitation\InvitationException;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use ContinuousPipe\Security\Team\Team;

class InMemoryUserInvitationRepository implements UserInvitationRepository
{
    /**
     * @var UserInvitation[]
     */
    private $invitations = [];

    /**
     * {@inheritdoc}
     */
    public function findByUserEmail($email)
    {
        return array_values(array_filter($this->invitations, function (UserInvitation $userInvitation) use ($email) {
            return $userInvitation->getUserEmail() == $email;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserInvitation $userInvitation)
    {
        return $this->invitations[] = $userInvitation;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UserInvitation $invitation)
    {
        if (false === ($index = array_search($invitation, $this->invitations))) {
            throw new InvitationException('Not found');
        }

        unset($this->invitations[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        return array_values(array_filter($this->invitations, function (UserInvitation $invitation) use ($team) {
            return $invitation->getTeamSlug() == $team->getSlug();
        }));
    }
}
